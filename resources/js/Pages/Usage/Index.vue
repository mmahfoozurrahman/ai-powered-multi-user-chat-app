<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLayout from '../../Components/Layout/AppLayout.vue';

const props = defineProps({
    aiSettings: {
        type: Object,
        default: () => ({}),
    },
});

const page = usePage();

const user = computed(() => page.props.auth?.user ?? {});
const monthlyTokens = computed(() => user.value.tokens_used_this_month ?? 0);
const plan = computed(() => user.value.plan ?? 'free');
const usage = computed(() => user.value.usage ?? {});
</script>

<template>
    <Head title="Usage" />

    <AppLayout
        page-title="Usage"
        page-description="Track your current plan and message/token footprint before we wire formal plan guards and scheduler resets."
    >
        <div class="row g-4">
            <div class="col-12 col-md-6 col-xxl-3">
                <div class="app-card p-4 workspace-metric app-lift">
                    <p class="workspace-metric__label">Current Plan</p>
                    <h2 class="workspace-metric__value text-capitalize">{{ plan }}</h2>
                    <p class="text-muted-soft mb-0">Free and Pro tiers are already modeled in the database.</p>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xxl-3">
                <div class="app-card p-4 workspace-metric app-lift">
                    <p class="workspace-metric__label">Monthly Tokens Used</p>
                    <h2 class="workspace-metric__value">{{ monthlyTokens }}</h2>
                    <p class="text-muted-soft mb-0">{{ usage.monthly_tokens_remaining ?? 0 }} tokens remain in the current free-tier budget.</p>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xxl-3">
                <div class="app-card p-4 workspace-metric app-lift">
                    <p class="workspace-metric__label">Daily Message Limit</p>
                    <h2 class="workspace-metric__value">{{ usage.daily_message_limit ?? aiSettings.free_daily_message_limit }}</h2>
                    <p class="text-muted-soft mb-0">{{ usage.daily_messages_remaining ?? 0 }} messages remain today on the free tier.</p>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xxl-3">
                <div class="app-card p-4 workspace-metric app-lift">
                    <p class="workspace-metric__label">Daily Tokens Left</p>
                    <h2 class="workspace-metric__value">{{ usage.daily_tokens_remaining ?? 0 }}</h2>
                    <p class="text-muted-soft mb-0">These update from saved message usage so users can see their remaining free-plan room.</p>
                </div>
            </div>

            <div class="col-12">
                <section class="app-card p-4 p-lg-5">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                        <div>
                            <p class="small text-muted-soft text-uppercase fw-semibold mb-1">Upcoming Guardrails</p>
                            <h2 class="h4 fw-bold mb-0">Limits and reset logic roadmap</h2>
                        </div>
                        <span class="status-badge">Phase Ready</span>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-lg-4">
                            <div class="border rounded-4 p-3 h-100">
                                <p class="small text-muted-soft mb-2">Active Model</p>
                                <p class="fw-semibold mb-0">{{ aiSettings.active_model }} with a {{ aiSettings.context_window }} token context window.</p>
                            </div>
                        </div>
                        <div class="col-12 col-lg-4">
                            <div class="border rounded-4 p-3 h-100">
                                <p class="small text-muted-soft mb-2">Free Plan Budget</p>
                                <p class="fw-semibold mb-0">{{ aiSettings.free_daily_token_limit }} daily tokens and {{ aiSettings.free_monthly_token_limit }} monthly tokens are currently configured.</p>
                            </div>
                        </div>
                        <div class="col-12 col-lg-4">
                            <div class="border rounded-4 p-3 h-100">
                                <p class="small text-muted-soft mb-2">Provider Reference</p>
                                <p class="fw-semibold mb-0">Reference limits are RPM {{ aiSettings.provider_reference_rpm ?? '—' }}, TPM {{ aiSettings.provider_reference_tpm ?? '—' }}, and TPD {{ aiSettings.provider_reference_tpd ?? '—' }}.</p>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </AppLayout>
</template>
