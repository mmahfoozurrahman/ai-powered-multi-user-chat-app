<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import GuestLayout from '../../Components/Layout/GuestLayout.vue';
import AppButton from '../../Components/UI/AppButton.vue';
import AppInput from '../../Components/UI/AppInput.vue';

const props = defineProps({
    token: {
        type: String,
        required: true,
    },
    email: {
        type: String,
        default: '',
    },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post('/reset-password', {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Reset Password" />

    <GuestLayout
        title="Set a fresh password and continue."
        eyebrow="Custom Authentication"
        description="Create a new password for your GroqChatInterface account and get back into your private workspace."
    >
        <form class="app-card p-4 p-lg-5 auth-form" @submit.prevent="submit">
            <div class="mb-4">
                <p class="text-muted-soft text-uppercase small fw-semibold mb-2">Secure Reset</p>
                <h2 class="h3 fw-bold mb-2">Choose a new password</h2>
                <p class="text-muted-soft mb-0">This reset link is time-sensitive, so finish the update before it expires.</p>
            </div>

            <div class="d-grid gap-3">
                <AppInput
                    id="reset-email"
                    v-model="form.email"
                    name="email"
                    type="email"
                    label="Email address"
                    autocomplete="email"
                    placeholder="developer@example.com"
                    :error="form.errors.email"
                />

                <AppInput
                    id="reset-password"
                    v-model="form.password"
                    name="password"
                    type="password"
                    label="New password"
                    autocomplete="new-password"
                    placeholder="Enter a new password"
                    :error="form.errors.password"
                />

                <AppInput
                    id="reset-password-confirmation"
                    v-model="form.password_confirmation"
                    name="password_confirmation"
                    type="password"
                    label="Confirm new password"
                    autocomplete="new-password"
                    placeholder="Repeat your new password"
                />
            </div>

            <AppButton
                type="submit"
                class="w-100 mt-4"
                icon="bi-shield-lock"
                :processing="form.processing"
            >
                Reset Password
            </AppButton>

            <p class="auth-form__footer mb-0 mt-4">
                Need to start again?
                <Link href="/forgot-password" class="auth-form__link">Request another link</Link>
            </p>
        </form>
    </GuestLayout>
</template>
