<script setup>
import { computed } from 'vue';

const props = defineProps({
    variant: {
        type: String,
        default: 'primary',
    },
    type: {
        type: String,
        default: 'button',
    },
    icon: {
        type: String,
        default: '',
    },
    processing: {
        type: Boolean,
        default: false,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

const classes = computed(() => `btn btn-${props.variant} d-inline-flex align-items-center justify-content-center gap-2`);
</script>

<template>
    <button :type="type" :class="classes" :disabled="disabled || processing">
        <span v-if="processing" class="spinner-border spinner-border-sm" aria-hidden="true"></span>
        <i v-else-if="icon" class="bi" :class="icon"></i>
        <slot />
    </button>
</template>
