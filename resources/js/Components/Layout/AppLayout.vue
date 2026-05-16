<script setup>
import { ref } from 'vue';
import Navbar from './Navbar.vue';
import Sidebar from './Sidebar.vue';
import { useFlashAlerts } from '../../Composables/useFlashAlerts';

defineProps({
    pageTitle: {
        type: String,
        default: '',
    },
    pageDescription: {
        type: String,
        default: '',
    },
});

useFlashAlerts();

const isSidebarOpen = ref(false);

const openSidebar = () => {
    isSidebarOpen.value = true;
};

const closeSidebar = () => {
    isSidebarOpen.value = false;
};
</script>

<template>
    <div class="app-surface">
        <div
            class="app-shell"
            :class="{ 'app-shell--sidebar-open': isSidebarOpen }"
        >
            <button
                type="button"
                class="app-shell__overlay d-xl-none"
                aria-label="Close sidebar"
                @click="closeSidebar"
            ></button>

            <Sidebar
                :open="isSidebarOpen"
                @close="closeSidebar"
            />

            <div class="app-shell__content">
                <Navbar
                    :title="pageTitle"
                    :description="pageDescription"
                    @toggle-sidebar="openSidebar"
                />
                <main class="p-3 p-lg-4 p-xl-5">
                    <slot />
                </main>
            </div>
        </div>
    </div>
</template>
