<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import GuestLayout from '../../Components/Layout/GuestLayout.vue';
import AppButton from '../../Components/UI/AppButton.vue';
import AppInput from '../../Components/UI/AppInput.vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post('/register', {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Register" />

    <GuestLayout
        title="Create your developer workspace."
        eyebrow="Custom Authentication"
        description="Start a clean workspace for Laravel, Vue, SQL, and AI-assisted problem solving with private conversation history."
    >
        <form class="app-card p-4 p-lg-5 auth-form" @submit.prevent="submit">
            <div class="mb-4">
                <p class="text-muted-soft text-uppercase small fw-semibold mb-2">Create Account</p>
                <h2 class="h3 fw-bold mb-2">Join GroqChatInterface</h2>
                <p class="text-muted-soft mb-0">Set up your developer profile and get ready for custom-auth protected chats.</p>
            </div>

            <div class="row g-3">
                <div class="col-12">
                    <AppInput
                        id="register-name"
                        v-model="form.name"
                        name="name"
                        label="Full name"
                        autocomplete="name"
                        placeholder="Jane Developer"
                        :error="form.errors.name"
                    />
                </div>
                <div class="col-12">
                    <AppInput
                        id="register-email"
                        v-model="form.email"
                        name="email"
                        type="email"
                        label="Email address"
                        autocomplete="email"
                        placeholder="jane@example.com"
                        :error="form.errors.email"
                    />
                </div>
                <div class="col-md-6">
                    <AppInput
                        id="register-password"
                        v-model="form.password"
                        name="password"
                        type="password"
                        label="Password"
                        autocomplete="new-password"
                        placeholder="Choose a secure password"
                        :error="form.errors.password"
                        help="Use at least 8 characters for a safer account."
                    />
                </div>
                <div class="col-md-6">
                    <AppInput
                        id="register-password-confirmation"
                        v-model="form.password_confirmation"
                        name="password_confirmation"
                        type="password"
                        label="Confirm password"
                        autocomplete="new-password"
                        placeholder="Repeat your password"
                    />
                </div>
            </div>

            <AppButton
                type="submit"
                class="w-100 mt-4"
                icon="bi-person-check"
                :processing="form.processing"
            >
                Create Account
            </AppButton>

            <p class="auth-form__footer mb-0 mt-4">
                Already registered?
                <Link href="/login" class="auth-form__link">Sign in here</Link>
            </p>
        </form>
    </GuestLayout>
</template>
