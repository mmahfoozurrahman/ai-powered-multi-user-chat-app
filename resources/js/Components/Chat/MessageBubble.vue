<script setup>
defineProps({
    role: {
        type: String,
        default: 'assistant',
    },
});
</script>

<template>
    <div class="message-bubble p-3" :class="role === 'user' ? 'message-bubble--user ms-auto' : 'message-bubble--assistant'">
        <slot />
    </div>
</template>
