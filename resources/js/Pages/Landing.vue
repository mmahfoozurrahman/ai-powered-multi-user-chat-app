<script setup>
import { Head } from '@inertiajs/vue3';
import GuestLayout from '../Components/Layout/GuestLayout.vue';
</script>

<template>
    <Head title="Build Faster" />

    <GuestLayout
        title="Build smarter with Groq-powered coding help."
        eyebrow="Private Developer SaaS"
        description="A focused AI workspace for Laravel, Vue, SQL, and product teams who need fast answers, reliable chat history, and clean collaboration."
    >
        <div class="d-grid gap-3">
            <div class="app-card p-4 p-lg-5">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div>
                        <p class="text-muted-soft small text-uppercase fw-semibold mb-1">Phase 1 Ready</p>
                        <h2 class="h4 fw-bold mb-0">Bootstrap foundation is in place.</h2>
                    </div>
                    <span class="status-badge">Bootstrap 5 + Vue 3</span>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="border rounded-4 p-3 h-100 app-lift">
                            <p class="small text-muted-soft mb-2">Frontend</p>
                            <p class="fw-semibold mb-0">Inertia.js, Vue 3, Bootstrap 5, SweetAlert2, SCSS</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded-4 p-3 h-100 app-lift">
                            <p class="small text-muted-soft mb-2">Backend</p>
                            <p class="fw-semibold mb-0">Laravel 12, custom auth path, Groq-ready HTTP service layer</p>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-column flex-sm-row gap-3">
                    <a href="/login" class="btn btn-primary w-100">
                        <i class="bi bi-box-arrow-in-right me-2"></i>
                        Sign In
                    </a>
                    <a href="/register" class="btn btn-outline-primary w-100">
                        <i class="bi bi-person-plus me-2"></i>
                        Create Account
                    </a>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>
