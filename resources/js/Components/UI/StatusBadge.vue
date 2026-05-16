<script setup>
defineProps({
    label: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <span class="status-badge">{{ label }}</span>
</template>
