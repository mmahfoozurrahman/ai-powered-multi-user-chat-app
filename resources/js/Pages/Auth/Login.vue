<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import GuestLayout from '../../Components/Layout/GuestLayout.vue';
import AppButton from '../../Components/UI/AppButton.vue';
import AppInput from '../../Components/UI/AppInput.vue';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post('/login', {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Login" />

    <GuestLayout
        title="Sign in to start coding smarter."
        eyebrow="Custom Authentication"
        description="Access your private AI coding workspace, revisit conversations, and continue building with Groq-powered assistance."
    >
        <form class="app-card p-4 p-lg-5 auth-form" @submit.prevent="submit">
            <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
                <div>
                    <p class="text-muted-soft text-uppercase small fw-semibold mb-2">Welcome Back</p>
                    <h2 class="h3 fw-bold mb-2">Sign in</h2>
                    <p class="text-muted-soft mb-0">Use your developer account to access chats and saved history.</p>
                </div>
                <span class="status-badge">Private Beta</span>
            </div>

            <div class="d-grid gap-3">
                <AppInput
                    id="login-email"
                    v-model="form.email"
                    name="email"
                    label="Email address"
                    type="email"
                    autocomplete="email"
                    placeholder="developer@example.com"
                    :error="form.errors.email"
                />

                <AppInput
                    id="login-password"
                    v-model="form.password"
                    name="password"
                    label="Password"
                    type="password"
                    autocomplete="current-password"
                    placeholder="Enter your password"
                    :error="form.errors.password"
                />
            </div>

            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 my-4">
                <label class="auth-form__checkbox">
                    <input v-model="form.remember" type="checkbox" class="form-check-input">
                    <span>Remember me</span>
                </label>

                <Link href="/forgot-password" class="auth-form__link">
                    Forgot password?
                </Link>
            </div>

            <AppButton
                type="submit"
                class="w-100"
                icon="bi-arrow-right-circle"
                :processing="form.processing"
            >
                Sign In
            </AppButton>

            <p class="auth-form__footer mb-0 mt-4">
                Need an account?
                <Link href="/register" class="auth-form__link">Create one here</Link>
            </p>
        </form>
    </GuestLayout>
</template>
