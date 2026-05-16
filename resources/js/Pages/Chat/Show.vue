<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '../../Components/Layout/AppLayout.vue';
import ChatWorkspace from '../../Components/Chat/ChatWorkspace.vue';

defineProps({
    conversations: {
        type: Array,
        default: () => [],
    },
    activeConversation: {
        type: Object,
        default: null,
    },
});
</script>

<template>
    <Head title="Conversation" />

    <AppLayout
        page-title="Conversation"
        page-description="Stay in context with one active thread while your saved conversations remain close at hand."
    >
        <ChatWorkspace
            :conversations="conversations"
            :active-conversation="activeConversation"
        />
    </AppLayout>
</template>
