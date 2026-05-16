<script setup>
import { useFlashAlerts } from '../../Composables/useFlashAlerts';

defineProps({
    title: {
        type: String,
        required: true,
    },
    eyebrow: {
        type: String,
        default: '',
    },
    description: {
        type: String,
        default: '',
    },
});

useFlashAlerts();
</script>

<template>
    <div class="guest-shell">
        <div class="container">
            <div class="guest-card row g-0">
                <div class="guest-card__hero col-lg-5 p-4 p-lg-5 d-flex flex-column justify-content-between">
                    <div>
                        <p v-if="eyebrow" class="guest-card__eyebrow mb-3">{{ eyebrow }}</p>
                        <h1 class="display-6 fw-bold mb-3">{{ title }}</h1>
                        <p class="mb-0 text-white-50">{{ description }}</p>
                    </div>

                    <div class="pt-5">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <span class="status-badge bg-white text-dark">Laravel 12</span>
                            <span class="status-badge bg-white text-dark">Vue 3</span>
                        </div>
                        <p class="small text-white-50 mb-0">
                            GroqChatInterface gives private teams a clean place to build, ask, and iterate faster.
                        </p>
                    </div>
                </div>

                <div class="col-lg-7 p-4 p-lg-5 d-flex align-items-center">
                    <div class="w-100">
                        <slot />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
