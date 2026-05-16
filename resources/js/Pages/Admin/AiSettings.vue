<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '../../Components/Layout/AppLayout.vue';
import AppButton from '../../Components/UI/AppButton.vue';
import AppInput from '../../Components/UI/AppInput.vue';

const props = defineProps({
    settings: {
        type: Object,
        default: () => ({}),
    },
    hasApiKey: {
        type: Boolean,
        default: false,
    },
});

const form = useForm({
    active_model: props.settings.active_model ?? 'qwen/qwen3-32b',
    groq_api_key: '',
    system_prompt: props.settings.system_prompt ?? '',
    context_window: props.settings.context_window ?? 131072,
    max_output_tokens: props.settings.max_output_tokens ?? 40960,
    reserved_completion_tokens: props.settings.reserved_completion_tokens ?? 2048,
    provider_reference_rpm: props.settings.provider_reference_rpm ?? 60,
    provider_reference_rpd: props.settings.provider_reference_rpd ?? 1000,
    provider_reference_tpm: props.settings.provider_reference_tpm ?? 6000,
    provider_reference_tpd: props.settings.provider_reference_tpd ?? 500000,
    free_daily_message_limit: props.settings.free_daily_message_limit ?? 100,
    free_daily_token_limit: props.settings.free_daily_token_limit ?? 12000,
    free_monthly_token_limit: props.settings.free_monthly_token_limit ?? 150000,
});

const submit = () => {
    form.patch('/admin/ai-settings');
};
</script>

<template>
    <Head title="Admin AI Settings" />

    <AppLayout
        page-title="Admin AI Settings"
        page-description="Control the active Groq model, reference limits, and the free-tier usage budget users see while chatting."
    >
        <form class="row g-4" @submit.prevent="submit">
            <div class="col-12 col-xl-7">
                <section class="app-card p-4 p-lg-5 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <p class="small text-muted-soft text-uppercase fw-semibold mb-1">Model Configuration</p>
                            <h2 class="h4 fw-bold mb-0">Active model and prompt budget</h2>
                        </div>
                        <span class="status-badge">Admin</span>
                    </div>

                    <div class="row g-3">
                        <div class="col-12">
                            <AppInput
                                id="active_model"
                                v-model="form.active_model"
                                label="Active Model ID"
                                placeholder="qwen/qwen3-32b"
                                :error="form.errors.active_model"
                                help="This model ID will be used by the Groq service for future chat requests."
                            />
                        </div>

                        <div class="col-12">
                            <AppInput
                                id="groq_api_key"
                                v-model="form.groq_api_key"
                                type="password"
                                label="Groq API Key"
                                placeholder="Leave blank to keep the current database key"
                                :error="form.errors.groq_api_key"
                                :help="hasApiKey ? 'A database-stored key is already active. Enter a new key only when you want to replace it.' : 'Add the provider key here so chat requests no longer depend on the .env file.'"
                            />
                        </div>

                        <div class="col-12">
                            <label for="system_prompt" class="form-label auth-form__label">System Prompt</label>
                            <textarea
                                id="system_prompt"
                                v-model="form.system_prompt"
                                class="form-control auth-form__input"
                                :class="{ 'is-invalid': form.errors.system_prompt }"
                                rows="10"
                                placeholder="Write the assistant instructions that should be prepended to every chat request."
                            ></textarea>
                            <div v-if="form.errors.system_prompt" class="auth-form__error">
                                <i class="bi bi-exclamation-circle me-2"></i>
                                {{ form.errors.system_prompt }}
                            </div>
                            <div v-else class="auth-form__help">
                                This prompt is stored in the database and applied to both standard and streaming responses.
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <AppInput
                                id="context_window"
                                v-model="form.context_window"
                                type="number"
                                label="Context Window"
                                :error="form.errors.context_window"
                                help="Maximum total tokens the selected model can accept."
                            />
                        </div>

                        <div class="col-12 col-md-6">
                            <AppInput
                                id="max_output_tokens"
                                v-model="form.max_output_tokens"
                                type="number"
                                label="Model Max Output"
                                :error="form.errors.max_output_tokens"
                                help="Reference maximum output tokens supported by the model."
                            />
                        </div>

                        <div class="col-12 col-md-6">
                            <AppInput
                                id="reserved_completion_tokens"
                                v-model="form.reserved_completion_tokens"
                                type="number"
                                label="Reserved Reply Tokens"
                                :error="form.errors.reserved_completion_tokens"
                                help="Reserved headroom for the assistant reply when prompt size is estimated."
                            />
                        </div>
                    </div>
                </section>
            </div>

            <div class="col-12 col-xl-5">
                <section class="app-card p-4 p-lg-5 h-100">
                    <p class="small text-muted-soft text-uppercase fw-semibold mb-1">Provider Reference</p>
                    <h2 class="h4 fw-bold mb-4">Groq free-tier reference limits</h2>

                    <div class="row g-3">
                        <div class="col-6">
                            <AppInput id="provider_reference_rpm" v-model="form.provider_reference_rpm" type="number" label="RPM" :error="form.errors.provider_reference_rpm" />
                        </div>
                        <div class="col-6">
                            <AppInput id="provider_reference_rpd" v-model="form.provider_reference_rpd" type="number" label="RPD" :error="form.errors.provider_reference_rpd" />
                        </div>
                        <div class="col-6">
                            <AppInput id="provider_reference_tpm" v-model="form.provider_reference_tpm" type="number" label="TPM" :error="form.errors.provider_reference_tpm" />
                        </div>
                        <div class="col-6">
                            <AppInput id="provider_reference_tpd" v-model="form.provider_reference_tpd" type="number" label="TPD" :error="form.errors.provider_reference_tpd" />
                        </div>
                    </div>
                </section>
            </div>

            <div class="col-12">
                <section class="app-card p-4 p-lg-5">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <p class="small text-muted-soft text-uppercase fw-semibold mb-1">Free Tier Budget</p>
                            <h2 class="h4 fw-bold mb-0">User-facing limits</h2>
                        </div>
                        <span class="status-badge">Visible In Chat</span>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <AppInput id="free_daily_message_limit" v-model="form.free_daily_message_limit" type="number" label="Daily Message Limit" :error="form.errors.free_daily_message_limit" />
                        </div>
                        <div class="col-12 col-md-4">
                            <AppInput id="free_daily_token_limit" v-model="form.free_daily_token_limit" type="number" label="Daily Token Limit" :error="form.errors.free_daily_token_limit" />
                        </div>
                        <div class="col-12 col-md-4">
                            <AppInput id="free_monthly_token_limit" v-model="form.free_monthly_token_limit" type="number" label="Monthly Token Limit" :error="form.errors.free_monthly_token_limit" />
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <AppButton type="submit" :processing="form.processing" class="px-4" icon="bi-save">
                            Save AI Settings
                        </AppButton>
                    </div>
                </section>
            </div>
        </form>
    </AppLayout>
</template>
