<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({
    open: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close']);

const page = usePage();

const user = computed(() => page.props.auth?.user);
const navItems = computed(() => {
    const items = [
        { label: 'Chats', icon: 'bi-chat-dots', href: '/chats' },
        { label: 'Settings', icon: 'bi-gear', href: '/settings' },
        { label: 'Usage', icon: 'bi-graph-up-arrow', href: '/usage' },
    ];

    if (user.value?.is_admin) {
        items.push({ label: 'Admin Users', icon: 'bi-people', href: '/admin/users' });
        items.push({ label: 'Admin AI', icon: 'bi-sliders', href: '/admin/ai-settings' });
    }

    return items;
});

const monthlyTokens = computed(() => user.value?.tokens_used_this_month ?? 0);
const plan = computed(() => (user.value?.plan ?? 'free').toUpperCase());
const activeModel = computed(() => page.props.ai?.model?.id ?? 'qwen/qwen3-32b');

const initials = computed(() => {
    if (!user.value?.name) {
        return 'DU';
    }

    return user.value.name
        .split(' ')
        .slice(0, 2)
        .map((part) => part.charAt(0).toUpperCase())
        .join('');
});

const profileImageUrl = computed(() => user.value?.profile_image_url ?? null);

const isActive = (href) => page.url === href || page.url.startsWith(`${href}/`);

const closeSidebar = () => {
    emit('close');
};
</script>

<template>
    <aside
        class="app-sidebar d-flex flex-column p-3 p-lg-4"
        :class="{ 'is-open': open }"
    >
        <div>
            <div class="d-flex justify-content-between align-items-center gap-3 mb-4">
                <div class="app-sidebar__brand d-flex align-items-center gap-2">
                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-white text-dark" style="width: 2.5rem; height: 2.5rem;">
                        <i class="bi bi-braces-asterisk"></i>
                    </span>
                    <div>
                        <div>AI Assistant</div>
                        <small class="text-white-50">GroqChatInterface</small>
                    </div>
                </div>

                <button type="button" class="btn btn-sm btn-outline-light d-xl-none" @click="closeSidebar">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <div class="app-sidebar__footer mb-4">
                <div class="app-sidebar__account-label">Signed In</div>

                <div class="app-sidebar__account mb-3">
                    <div class="app-sidebar__avatar">
                        <img
                            v-if="profileImageUrl"
                            :src="profileImageUrl"
                            :alt="user?.name || 'Developer User'"
                        >
                        <span v-else>{{ initials }}</span>
                    </div>

                    <div class="app-sidebar__identity">
                        <div class="app-sidebar__name-row">
                            <div class="app-sidebar__name">{{ user?.name || 'Developer User' }}</div>
                            <span class="app-sidebar__mini-pill">{{ plan }}</span>
                        </div>

                        <div class="app-sidebar__email" :title="user?.email || 'developer@example.com'">
                            {{ user?.email || 'developer@example.com' }}
                        </div>

                        <div class="app-sidebar__meta">
                            <i class="bi bi-shield-check"></i>
                            <span>Private workspace account</span>
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <Link href="/settings" class="app-sidebar__action-link" @click="closeSidebar">
                        <i class="bi bi-person-gear"></i>
                        Manage profile
                    </Link>
                    <Link href="/logout" method="post" as="button" class="app-sidebar__logout">
                        <i class="bi bi-box-arrow-right"></i>
                        Logout
                    </Link>
                </div>
            </div>

            <div class="app-sidebar__panel mb-4">
                <p class="small text-uppercase fw-semibold text-white-50 mb-2">Workspace Snapshot</p>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-white-50">Plan</span>
                    <span class="app-sidebar__pill">{{ plan }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-white-50">Tokens this month</span>
                    <strong class="text-white">{{ monthlyTokens }}</strong>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-white-50">Model</span>
                    <strong class="text-white app-sidebar__model">{{ activeModel }}</strong>
                </div>
            </div>

            <nav class="d-grid gap-2">
                <Link
                    v-for="item in navItems"
                    :key="item.label"
                    :href="item.href"
                    class="app-sidebar__nav-link"
                    :class="{ 'is-active': isActive(item.href) }"
                    @click="closeSidebar"
                >
                    <i class="bi" :class="item.icon"></i>
                    <span>{{ item.label }}</span>
                </Link>
            </nav>
        </div>
    </aside>
</template>
