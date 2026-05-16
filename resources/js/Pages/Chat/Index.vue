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
    <Head title="Chats" />

    <AppLayout
        page-title="Chats"
        page-description="Browse prior conversations, start a new coding session, and keep your workspace organized."
    >
        <ChatWorkspace
            :conversations="conversations"
            :active-conversation="activeConversation"
        />
    </AppLayout>
</template>
