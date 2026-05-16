<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    conversations: {
        type: Array,
        default: () => [],
    },
    activeConversationId: {
        type: Number,
        default: null,
    },
});

const formatDate = (value) => {
    if (!value) {
        return 'Just now';
    }

    return new Intl.DateTimeFormat('en', {
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    }).format(new Date(value));
};
</script>

<template>
    <aside class="app-card p-3 p-lg-4 conversation-sidebar">
        <Link href="/conversations" method="post" as="button" class="btn btn-primary w-100 mb-3">
            <i class="bi bi-plus-circle me-2"></i>
            New Chat
        </Link>

        <div class="conversation-sidebar__list">
            <div class="d-grid gap-3">
                <template v-if="conversations.length">
                    <div
                        v-for="conversation in conversations"
                        :key="conversation.id"
                        class="conversation-list-item border rounded-4 p-3"
                        :class="{ 'bg-light': conversation.id === activeConversationId }"
                    >
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <Link :href="`/chats/${conversation.id}`" class="conversation-list-item__link">
                                <p class="fw-semibold mb-1">{{ conversation.title }}</p>
                                <small class="text-muted-soft">{{ formatDate(conversation.updated_at) }}</small>
                            </Link>

                            <Link
                                :href="`/conversations/${conversation.id}`"
                                method="delete"
                                as="button"
                                class="conversation-list-item__delete"
                            >
                                <i class="bi bi-trash3"></i>
                            </Link>
                        </div>
                    </div>
                </template>

                <div v-else class="conversation-empty-state">
                    <div class="conversation-empty-state__icon">
                        <i class="bi bi-chat-square-heart"></i>
                    </div>
                    <h3 class="h6 fw-bold mb-2">No chats yet</h3>
                    <p class="text-muted-soft mb-0">
                        Start a new conversation to ask about Laravel, Vue, SQL, or your next feature.
                    </p>
                </div>
            </div>
        </div>
    </aside>
</template>
