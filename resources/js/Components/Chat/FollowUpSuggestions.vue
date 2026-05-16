<script setup>
defineProps({
    suggestions: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['select']);
</script>

<template>
    <div v-if="suggestions.length" class="d-flex flex-wrap gap-2">
        <button
            v-for="suggestion in suggestions"
            :key="suggestion"
            type="button"
            class="follow-up-pill btn btn-outline-primary btn-sm rounded-pill"
            @click="emit('select', suggestion)"
        >
            {{ suggestion }}
        </button>
    </div>
</template>
