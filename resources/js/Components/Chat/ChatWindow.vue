<script setup>
import { nextTick, ref, watch } from 'vue';
import FollowUpSuggestions from './FollowUpSuggestions.vue';
import MessageBubble from './MessageBubble.vue';
import { renderRichText } from '../../Utils/renderRichText';

const props = defineProps({
    activeConversation: {
        type: Object,
        default: null,
    },
    isStreaming: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['send-suggestion']);

const formatDate = (value) => {
    if (!value) {
        return '';
    }

    return new Intl.DateTimeFormat('en', {
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    }).format(new Date(value));
};

const messagesEnd = ref(null);

const scrollToBottom = async () => {
    await nextTick();
    // window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
    messagesEnd.value?.scrollIntoView({ behavior: 'smooth', block: 'end' });
};

const fallbackCopy = async (text) => {
    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.setAttribute('readonly', '');
    textarea.style.position = 'absolute';
    textarea.style.left = '-9999px';
    document.body.appendChild(textarea);
    textarea.select();
    document.execCommand('copy');
    document.body.removeChild(textarea);
};

const flashCopyState = (button, label) => {
    const original = button.dataset.originalLabel || button.textContent || 'Copy code';
    button.dataset.originalLabel = original;
    button.textContent = label;
    button.classList.add('is-copied');

    window.setTimeout(() => {
        button.textContent = original;
        button.classList.remove('is-copied');
    }, 1800);
};

const handleMessageInteraction = async (event) => {
    const button = event.target.closest('[data-copy-code]');

    if (!button) {
        return;
    }

    const code = button.closest('.chat-code-block')?.querySelector('code')?.innerText ?? '';

    if (!code.trim()) {
        return;
    }

    try {
        if (navigator?.clipboard?.writeText) {
            await navigator.clipboard.writeText(code);
        } else {
            await fallbackCopy(code);
        }

        flashCopyState(button, 'Copied');
    } catch {
        flashCopyState(button, 'Try again');
    }
};

watch(() => props.activeConversation?.messages?.map((message) => message.content).join('|'), scrollToBottom);
</script>

<template>
    <section class="app-card p-4 h-100">
        <div v-if="activeConversation" class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <p class="text-muted-soft text-uppercase small fw-semibold mb-1">Current Conversation</p>
                <h2 class="h4 fw-bold mb-1">{{ activeConversation.title }}</h2>
                <p class="text-muted-soft mb-0">
                    {{ activeConversation.messages.length }} message{{ activeConversation.messages.length === 1 ? '' : 's' }}
                </p>
            </div>
            <span class="status-badge">{{ isStreaming ? 'Streaming...' : formatDate(activeConversation.updated_at) }}</span>
        </div>

        <div v-if="activeConversation" class="d-grid gap-3 chat-window__messages">
            <div v-for="message in activeConversation.messages" :key="message.id" class="d-grid gap-2">
                <div class="d-flex" :class="message.role === 'user' ? 'justify-content-end' : 'justify-content-start'">
                    <MessageBubble :role="message.role">
                        <div
                            v-if="message.role === 'assistant'"
                            class="chat-rich-text"
                            @click="handleMessageInteraction"
                            v-html="renderRichText(message.content)"
                        ></div>
                        <div v-else class="chat-window__text">{{ message.content }}</div>
                    </MessageBubble>
                </div>

                <div class="d-flex" :class="message.role === 'user' ? 'justify-content-end' : 'justify-content-start'">
                    <small class="text-muted-soft">{{ formatDate(message.created_at) }}</small>
                </div>

                <FollowUpSuggestions
                    v-if="message.role === 'assistant' && message.follow_ups?.length"
                    :suggestions="message.follow_ups"
                    @select="emit('send-suggestion', $event)"
                />
            </div>
            <div ref="messagesEnd"></div>
        </div>

        <div v-else class="chat-empty-state">
            <div class="chat-empty-state__icon">
                <i class="bi bi-stars"></i>
            </div>
            <h2 class="h3 fw-bold mb-3">Start a conversation</h2>
            <p class="text-muted-soft mb-0">
                Create a new chat from the left panel to begin asking GroqChatInterface about Laravel, Vue, MySQL, data analysis, or your current build.
            </p>
        </div>
    </section>
</template>
