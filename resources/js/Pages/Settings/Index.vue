<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, ref } from 'vue';
import AppLayout from '../../Components/Layout/AppLayout.vue';
import AppButton from '../../Components/UI/AppButton.vue';
import AppInput from '../../Components/UI/AppInput.vue';

const props = defineProps({
    aiSettings: {
        type: Object,
        default: () => ({}),
    },
    hasApiKey: {
        type: Boolean,
        default: false,
    },
});

const page = usePage();
const user = computed(() => page.props.auth?.user ?? {});
const isAdmin = computed(() => user.value?.is_admin ?? false);
const previewUrl = ref(user.value?.profile_image_url ?? '');

const profileForm = useForm({
    name: user.value?.name ?? '',
    email: user.value?.email ?? '',
    profile_image: null,
});

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

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

const revokePreview = () => {
    if (previewUrl.value?.startsWith('blob:')) {
        URL.revokeObjectURL(previewUrl.value);
    }
};

const handleImageChange = (event) => {
    const [file] = event.target.files ?? [];

    profileForm.profile_image = file ?? null;
    revokePreview();
    previewUrl.value = file ? URL.createObjectURL(file) : (user.value?.profile_image_url ?? '');
};

const submitProfile = () => {
    profileForm.patch('/settings/profile', {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            profileForm.reset('profile_image');
        },
    });
};

const submitPassword = () => {
    passwordForm.patch('/settings/password', {
        preserveScroll: true,
        onSuccess: () => {
            passwordForm.reset();
        },
    });
};

onBeforeUnmount(() => {
    revokePreview();
});
</script>

<template>
    <Head title="Settings" />

    <AppLayout
        page-title="Settings"
        page-description="Manage your profile, account security, and the workspace defaults behind your private coding environment."
    >
        <div class="row g-4">
            <div class="col-12 col-lg-7">
                <section class="app-card p-4 p-lg-5">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                        <div>
                            <p class="small text-muted-soft text-uppercase fw-semibold mb-1">Profile</p>
                            <h2 class="h4 fw-bold mb-0">Your workspace identity</h2>
                        </div>
                        <span class="status-badge">{{ user.plan?.toUpperCase() || 'FREE' }}</span>
                    </div>

                    <form class="row g-4 align-items-start" @submit.prevent="submitProfile">
                        <div class="col-12 col-md-5">
                            <div class="profile-panel">
                                <div class="profile-panel__avatar">
                                    <img v-if="previewUrl" :src="previewUrl" :alt="user.name">
                                    <span v-else>{{ initials }}</span>
                                </div>
                                <div>
                                    <div class="fw-semibold fs-5">{{ user.name }}</div>
                                    <div class="text-muted-soft">{{ user.email }}</div>
                                </div>
                                <label for="profile_image" class="btn btn-outline-secondary w-100 mt-2">
                                    <i class="bi bi-camera me-2"></i>
                                    Update profile image
                                </label>
                                <input id="profile_image" type="file" accept="image/*" class="d-none" @change="handleImageChange">
                                <div v-if="profileForm.errors.profile_image" class="auth-form__error w-100">
                                    <i class="bi bi-exclamation-circle me-2"></i>
                                    {{ profileForm.errors.profile_image }}
                                </div>
                                <div v-else class="auth-form__help text-center">
                                    Uploaded images are stored with a datetime prefix for predictable file naming.
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-7">
                            <div class="row g-3">
                                <div class="col-12">
                                    <AppInput
                                        id="profile_name"
                                        v-model="profileForm.name"
                                        label="Name"
                                        placeholder="Your full name"
                                        :error="profileForm.errors.name"
                                    />
                                </div>
                                <div class="col-12">
                                    <AppInput
                                        id="profile_email"
                                        v-model="profileForm.email"
                                        type="email"
                                        label="Email"
                                        placeholder="you@example.com"
                                        :error="profileForm.errors.email"
                                    />
                                </div>
                                <div class="col-12 d-flex justify-content-end">
                                    <AppButton type="submit" :processing="profileForm.processing" icon="bi-save" class="px-4">
                                        Save profile
                                    </AppButton>
                                </div>
                            </div>
                        </div>
                    </form>
                </section>
            </div>

            <div class="col-12 col-lg-5">
                <section class="app-card p-4 p-lg-5 h-100">
                    <p class="small text-muted-soft text-uppercase fw-semibold mb-2">Security</p>
                    <h2 class="h4 fw-bold mb-3">Password and access</h2>
                    <p class="text-muted-soft mb-4">
                        Keep your account details current and rotate your password whenever you need a fresh credential.
                    </p>

                    <form class="d-grid gap-3" @submit.prevent="submitPassword">
                        <AppInput
                            id="current_password"
                            v-model="passwordForm.current_password"
                            type="password"
                            label="Current Password"
                            :error="passwordForm.errors.current_password"
                        />
                        <AppInput
                            id="new_password"
                            v-model="passwordForm.password"
                            type="password"
                            label="New Password"
                            :error="passwordForm.errors.password"
                        />
                        <AppInput
                            id="new_password_confirmation"
                            v-model="passwordForm.password_confirmation"
                            type="password"
                            label="Confirm New Password"
                        />

                        <div class="d-flex justify-content-end pt-2">
                            <AppButton type="submit" :processing="passwordForm.processing" icon="bi-shield-lock" class="px-4">
                                Update password
                            </AppButton>
                        </div>
                    </form>
                </section>
            </div>

            <div class="col-12">
                <section class="app-card p-4 p-lg-5">
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
                        <div>
                            <p class="small text-muted-soft text-uppercase fw-semibold mb-1">Workspace Preferences</p>
                            <h2 class="h4 fw-bold mb-0">Assistant defaults</h2>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <span class="status-badge">{{ aiSettings.active_model || 'Active Model' }}</span>
                            <span v-if="hasApiKey" class="status-badge">Database API Key Active</span>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-md-6 col-xl-3">
                            <div class="border rounded-4 p-3 app-lift h-100">
                                <p class="small text-muted-soft mb-2">Primary Model</p>
                                <p class="fw-semibold mb-0">{{ aiSettings.active_model }}</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-xl-3">
                            <div class="border rounded-4 p-3 app-lift h-100">
                                <p class="small text-muted-soft mb-2">Context Window</p>
                                <p class="fw-semibold mb-0">{{ aiSettings.context_window }} tokens</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-xl-3">
                            <div class="border rounded-4 p-3 app-lift h-100">
                                <p class="small text-muted-soft mb-2">Free Daily Token Limit</p>
                                <p class="fw-semibold mb-0">{{ aiSettings.free_daily_token_limit }} tokens</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-xl-3">
                            <div class="border rounded-4 p-3 app-lift h-100">
                                <p class="small text-muted-soft mb-2">Free Monthly Token Limit</p>
                                <p class="fw-semibold mb-0">{{ aiSettings.free_monthly_token_limit }} tokens</p>
                            </div>
                        </div>
                    </div>

                    <div v-if="isAdmin" class="d-flex gap-2 flex-wrap mt-4">
                        <Link href="/admin/ai-settings" class="btn btn-primary">
                            Open Admin AI Settings
                        </Link>
                        <Link href="/admin/users" class="btn btn-outline-secondary">
                            Manage Users
                        </Link>
                    </div>
                </section>
            </div>
        </div>
    </AppLayout>
</template>
