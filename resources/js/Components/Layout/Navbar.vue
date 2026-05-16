<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

defineProps({
    title: {
        type: String,
        default: '',
    },
    description: {
        type: String,
        default: '',
    },
});

defineEmits(['toggle-sidebar']);

const page = usePage();

const user = computed(() => page.props.auth?.user ?? {});
const planLabel = computed(() => (user.value.plan ?? 'free').toUpperCase());
</script>

<template>
    <header class="app-navbar px-3 px-lg-4 py-3 sticky-top">
        <div class="d-flex align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-start gap-3">
                <button
                    type="button"
                    class="btn btn-light border d-xl-none mt-1"
                    aria-label="Open sidebar"
                    @click="$emit('toggle-sidebar')"
                >
                    <i class="bi bi-list"></i>
                </button>

                <div>
                    <p class="small text-muted-soft text-uppercase fw-semibold mb-1">Workspace</p>
                    <h1 class="h4 fw-bold mb-1">{{ title || 'Dashboard' }}</h1>
                    <p v-if="description" class="text-muted-soft mb-0">{{ description }}</p>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2 flex-wrap justify-content-end">
                <span class="status-badge">Private Beta</span>
                <span class="status-badge">{{ planLabel }}</span>
            </div>
        </div>
    </header>
</template>
