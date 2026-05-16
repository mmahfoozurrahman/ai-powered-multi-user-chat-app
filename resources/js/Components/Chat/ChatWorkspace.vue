<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed, reactive, ref, watch } from 'vue';
import ChatWindow from './ChatWindow.vue';
import ConversationSidebar from './ConversationSidebar.vue';
import MessageInput from './MessageInput.vue';

const props = defineProps({
    conversations: {
        type: Array,
        default: () => [],
    },
    activeConversation: {
        type: Object,
        default: null,
    },
});

const page = usePage();

const localConversations = ref([]);
const localConversation = ref(null);
const isStreaming = ref(false);
const activeSubmittedPrompt = ref('');

const syncState = () => {
    localConversations.value = props.conversations.map((conversation) => ({ ...conversation }));
    localConversation.value = props.activeConversation
        ? {
            ...props.activeConversation,
            messages: props.activeConversation.messages.map((message) => ({ ...message })),
        }
        : null;
    isStreaming.value = false;
    activeSubmittedPrompt.value = '';
};

watch(() => [props.conversations, props.activeConversation], syncState, { immediate: true });

const csrfToken = computed(() => page.props.csrf_token || document.querySelector('meta[name="csrf-token"]')?.content || '');

const updateConversationList = (conversation) => {
    const existing = localConversations.value.find((item) => item.id === conversation.id);

    if (existing) {
        existing.title = conversation.title;
        existing.updated_at = conversation.updated_at;
    } else {
        localConversations.value.unshift(conversation);
    }

    localConversations.value = [...localConversations.value].sort(
        (left, right) => new Date(right.updated_at || 0) - new Date(left.updated_at || 0)
    );
};

const showToast = (icon, title) => {
    if (!window.Swal) {
        return;
    }

    window.Swal.fire({
        toast: true,
        position: 'top-end',
        timer: 3200,
        timerProgressBar: true,
        showConfirmButton: false,
        icon,
        title,
    });
};

const parseSseEvent = (rawEvent) => {
    const lines = rawEvent.split(/\r?\n/);
    let event = 'message';
    const dataLines = [];

    for (const line of lines) {
        if (line.startsWith('event:')) {
            event = line.slice(6).trim();
        }

        if (line.startsWith('data:')) {
            dataLines.push(line.slice(5).trim());
        }
    }

    return {
        event,
        data: dataLines.join('\n'),
    };
};

const parsePayload = (raw) => {
    if (!raw) {
        return {};
    }

    try {
        return JSON.parse(raw);
    } catch {
        return {};
    }
};

const streamMessage = async (content) => {
    if (!localConversation.value?.id || isStreaming.value) {
        return;
    }

    const message = content.trim();

    if (!message) {
        return;
    }

    isStreaming.value = true;
    activeSubmittedPrompt.value = message;

    const timestamp = new Date().toISOString();
    const conversationId = localConversation.value.id;

    const userMessage = reactive({
        id: `user-temp-${Date.now()}`,
        role: 'user',
        content: message,
        follow_ups: [],
        created_at: timestamp,
        tokens_used: 0,
    });

    const assistantMessage = reactive({
        id: `assistant-temp-${Date.now()}`,
        role: 'assistant',
        content: '',
        follow_ups: [],
        created_at: timestamp,
        tokens_used: 0,
        is_streaming: true,
    });

    localConversation.value.messages.push(userMessage, assistantMessage);

    if (localConversation.value.title === 'New Chat') {
        localConversation.value.title = `${message.slice(0, 57)}${message.length > 57 ? '...' : ''}`;
    }

    updateConversationList({
        id: localConversation.value.id,
        title: localConversation.value.title,
        updated_at: timestamp,
    });

    let pendingContent = '';
    let pendingCompletion = null;
    let flushTimer = null;

    const stopFlushLoop = () => {
        if (flushTimer) {
            window.clearInterval(flushTimer);
            flushTimer = null;
        }
    };

    const applyCompletion = (payload) => {
        assistantMessage.id = payload.message.id;
        assistantMessage.content = payload.message.content;
        assistantMessage.follow_ups = payload.message.follow_ups || [];
        assistantMessage.created_at = payload.message.created_at;
        assistantMessage.tokens_used = payload.message.tokens_used;
        assistantMessage.is_streaming = false;

        localConversation.value.title = payload.conversation.title;
        localConversation.value.updated_at = payload.conversation.updated_at;

        updateConversationList(payload.conversation);

        if (page.props.auth?.user) {
            page.props.auth.user.tokens_used_this_month = payload.tokens_used_this_month;
            page.props.auth.user.usage = payload.usage;
        }

        showToast('success', 'Groq responded successfully.');
        isStreaming.value = false;
    };

    const startFlushLoop = () => {
        if (flushTimer) {
            return;
        }

        flushTimer = window.setInterval(() => {
            if (pendingContent.length > 0) {
                const chunkSize = pendingContent.length > 120 ? 28 : pendingContent.length > 40 ? 16 : 8;
                assistantMessage.content += pendingContent.slice(0, chunkSize);
                pendingContent = pendingContent.slice(chunkSize);
            }

            if (pendingContent.length === 0 && pendingCompletion) {
                const payload = pendingCompletion;
                pendingCompletion = null;
                stopFlushLoop();
                applyCompletion(payload);
            }

            if (pendingContent.length === 0 && !pendingCompletion && !isStreaming.value) {
                stopFlushLoop();
            }
        }, 28);
    };
    try {
        const response = await fetch(`/conversations/${conversationId}/messages/stream`, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Accept': 'text/event-stream',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken.value,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ content: message }),
        });

        if (!response.ok || !response.body) {
            const errorPayload = await response
                .clone()
                .json()
                .catch(async () => ({ message: await response.text().catch(() => '') }));

            throw new Error(errorPayload.message || 'Streaming could not be started.');
        }

        const reader = response.body.getReader();
        const decoder = new TextDecoder();
        let buffer = '';

        while (true) {
            const { done, value } = await reader.read();

            if (done) {
                break;
            }

            buffer += decoder.decode(value, { stream: true }).replace(/\r\n/g, '\n');

            let delimiterIndex;

            while ((delimiterIndex = buffer.indexOf('\n\n')) !== -1) {
                const rawEvent = buffer.slice(0, delimiterIndex);
                buffer = buffer.slice(delimiterIndex + 2);

                if (!rawEvent.trim()) {
                    continue;
                }

                const parsed = parseSseEvent(rawEvent);
                const payload = parsePayload(parsed.data);

                if (parsed.event === 'chunk') {
                    pendingContent += payload.content || '';
                    startFlushLoop();
                }

                if (parsed.event === 'complete') {
                    pendingCompletion = payload;

                    if (!pendingContent.length) {
                        stopFlushLoop();
                        applyCompletion(payload);
                    }

                    return;
                }

                if (parsed.event === 'error') {
                    throw new Error(payload.message || 'Streaming failed.');
                }
            }
        }

        if (buffer.trim()) {
            const parsed = parseSseEvent(buffer.trim());
            const payload = parsePayload(parsed.data);

            if (parsed.event === 'complete') {
                pendingCompletion = payload;

                if (!pendingContent.length) {
                    stopFlushLoop();
                    applyCompletion(payload);
                }
            }
        }
    } catch (error) {
        stopFlushLoop();
        localConversation.value.messages = localConversation.value.messages.filter(
            (item) => item.id !== assistantMessage.id && item.id !== userMessage.id
        );

        showToast('error', error.message || 'Streaming failed.');
    } finally {
        assistantMessage.is_streaming = false;

        if (!pendingCompletion) {
            isStreaming.value = false;
        }
    }
};

const handleSuggestion = (suggestion) => {
    streamMessage(suggestion);
};

const activeConversationId = computed(() => localConversation.value?.id ?? null);
</script>

<template>
    <div class="row g-4 align-items-stretch">
        <div class="col-12 col-xl-4 col-xxl-3 order-2 order-xl-1">
            <ConversationSidebar
                :conversations="localConversations"
                :active-conversation-id="activeConversationId"
            />
        </div>
        <div class="col-12 col-xl-8 col-xxl-9 order-1 order-xl-2">
            <div class="d-grid gap-4">
                <ChatWindow
                    :active-conversation="localConversation"
                    :is-streaming="isStreaming"
                    @send-suggestion="handleSuggestion"
                />
                <MessageInput
                    :conversation-id="activeConversationId"
                    :active-conversation="localConversation"
                    :active-submitted-prompt="activeSubmittedPrompt"
                    :is-streaming="isStreaming"
                    @send="streamMessage"
                />
            </div>
        </div>
    </div>
</template>
