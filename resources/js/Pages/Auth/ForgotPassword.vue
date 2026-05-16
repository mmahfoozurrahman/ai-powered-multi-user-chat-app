<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import GuestLayout from '../../Components/Layout/GuestLayout.vue';
import AppButton from '../../Components/UI/AppButton.vue';
import AppInput from '../../Components/UI/AppInput.vue';

const form = useForm({
    email: '',
});

const submit = () => {
    form.post('/forgot-password', {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Forgot Password" />

    <GuestLayout
        title="Recover access to your workspace."
        eyebrow="Custom Authentication"
        description="We’ll generate a secure reset link and send it to the email address attached to your account."
    >
        <form class="app-card p-4 p-lg-5 auth-form" @submit.prevent="submit">
            <div class="mb-4">
                <p class="text-muted-soft text-uppercase small fw-semibold mb-2">Password Recovery</p>
                <h2 class="h3 fw-bold mb-2">Forgot your password?</h2>
                <p class="text-muted-soft mb-0">Enter your email and we’ll send a reset link if the account exists.</p>
            </div>

            <AppInput
                id="forgot-email"
                v-model="form.email"
                name="email"
                type="email"
                label="Email address"
                autocomplete="email"
                placeholder="developer@example.com"
                :error="form.errors.email"
            />

            <AppButton
                type="submit"
                class="w-100 mt-4"
                icon="bi-envelope-check"
                :processing="form.processing"
            >
                Send Reset Link
            </AppButton>

            <p class="auth-form__footer mb-0 mt-4">
                Remembered it?
                <Link href="/login" class="auth-form__link">Back to sign in</Link>
            </p>
        </form>
    </GuestLayout>
</template>
