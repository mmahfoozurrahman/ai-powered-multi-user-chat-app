<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import AppButton from '../UI/AppButton.vue';
import { estimateMessageTokens, estimateStringTokens } from '../../Utils/estimateTokens';

const props = defineProps({
    conversationId: {
        type: Number,
        default: null,
    },
    activeConversation: {
        type: Object,
        default: null,
    },
    activeSubmittedPrompt: {
        type: String,
        default: '',
    },
    isStreaming: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['send']);

const page = usePage();
const content = ref('');

const ai = computed(() => page.props.ai ?? {});
const user = computed(() => page.props.auth?.user ?? {});
const usage = computed(() => user.value?.usage ?? {});
const model = computed(() => ai.value.model ?? {});
const providerLimits = computed(() => ai.value.provider_limits ?? {});
const systemPromptTokens = computed(() => model.value.system_prompt_tokens ?? 0);
const historyTokens = computed(() => estimateMessageTokens(props.activeConversation?.messages ?? []));
const promptPreview = computed(() => content.value.trim() || props.activeSubmittedPrompt || '');
const inputTokens = computed(() => estimateStringTokens(promptPreview.value));
const estimatedPromptTokens = computed(() => systemPromptTokens.value + historyTokens.value + inputTokens.value);
const maxPromptTokens = computed(() => model.value.max_prompt_tokens ?? 0);
const providerTpmLimit = computed(() => providerLimits.value.tpm ?? 0);
const remainingDailyTokens = computed(() => usage.value.daily_tokens_remaining ?? 0);
const remainingMonthlyTokens = computed(() => usage.value.monthly_tokens_remaining ?? 0);
const willExceedContext = computed(() => maxPromptTokens.value > 0 && estimatedPromptTokens.value > maxPromptTokens.value);
const willExceedProviderTpm = computed(() => providerTpmLimit.value > 0 && estimatedPromptTokens.value > providerTpmLimit.value);
const willExceedDaily = computed(() => user.value?.plan === 'free' && estimatedPromptTokens.value > remainingDailyTokens.value);
const willExceedMonthly = computed(() => user.value?.plan === 'free' && estimatedPromptTokens.value > remainingMonthlyTokens.value);
const isSendDisabled = computed(() => {
    return !props.conversationId
        || !content.value.trim()
        || props.isStreaming
        || willExceedContext.value
        || willExceedProviderTpm.value
        || willExceedDaily.value
        || willExceedMonthly.value;
});

const submit = () => {
    if (isSendDisabled.value) {
        return;
    }

    emit('send', content.value);
    content.value = '';
};

watch(() => props.conversationId, () => {
    content.value = '';
});
</script>

<template>
    <form class="app-card p-3" @submit.prevent="submit">
        <div class="message-input__composer">
            <div class="message-input__field">
                <textarea
                    v-model="content"
                    class="form-control message-input__control"
                    rows="3"
                    :disabled="!conversationId || isStreaming"
                    placeholder="Ask GroqChatInterface anything about Laravel, Vue, SQL, or your next feature..."
                ></textarea>
                <div v-if="!conversationId" class="auth-form__help mt-2">
                    Start a new chat from the left panel to send your first message.
                </div>
                <div v-else-if="isStreaming" class="auth-form__help mt-2">
                    Groq is responding live. You can send the next message as soon as the stream finishes.
                </div>
                <div v-else-if="willExceedContext" class="auth-form__error mt-2">
                    <i class="bi bi-exclamation-circle me-2"></i>
                    This message would push the conversation beyond the configured prompt budget for the active model.
                </div>
                <div v-else-if="willExceedProviderTpm" class="auth-form__error mt-2">
                    <i class="bi bi-exclamation-circle me-2"></i>
                    This request is larger than the configured TPM safety limit of {{ providerTpmLimit }} tokens.
                </div>
                <div v-else-if="willExceedDaily" class="auth-form__error mt-2">
                    <i class="bi bi-exclamation-circle me-2"></i>
                    This prompt is larger than the free-plan tokens remaining for today.
                </div>
                <div v-else-if="willExceedMonthly" class="auth-form__error mt-2">
                    <i class="bi bi-exclamation-circle me-2"></i>
                    This prompt is larger than the free-plan tokens remaining this month.
                </div>
                <div class="message-budget mt-3">
                    <div class="message-budget__item">
                        <span class="message-budget__label">Model</span>
                        <strong>{{ model.id || 'Not configured' }}</strong>
                    </div>
                    <div class="message-budget__item">
                        <span class="message-budget__label">Typed Prompt</span>
                        <strong>~{{ inputTokens }} tokens</strong>
                    </div>
                    <div class="message-budget__item">
                        <span class="message-budget__label">Conversation Context</span>
                        <strong>~{{ historyTokens + systemPromptTokens }} tokens</strong>
                    </div>
                    <div class="message-budget__item">
                        <span class="message-budget__label">Estimated Prompt Sent</span>
                        <strong>~{{ estimatedPromptTokens }} / {{ maxPromptTokens || model.context_window || 0 }}</strong>
                    </div>
                    <div class="message-budget__item">
                        <span class="message-budget__label">Free Tokens Left Today</span>
                        <strong>{{ remainingDailyTokens }}</strong>
                    </div>
                    <div class="message-budget__item">
                        <span class="message-budget__label">Free Tokens Left This Month</span>
                        <strong>{{ remainingMonthlyTokens }}</strong>
                    </div>
                    <div class="message-budget__item">
                        <span class="message-budget__label">Provider TPM Reference</span>
                        <strong>{{ providerLimits.tpm ?? '—' }}</strong>
                    </div>
                    <div class="message-budget__item">
                        <span class="message-budget__label">Daily Messages Left</span>
                        <strong>{{ usage.daily_messages_remaining ?? 0 }}</strong>
                    </div>
                </div>
            </div>

            <AppButton
                type="submit"
                class="px-4 message-input__button"
                icon="bi-send-fill"
                :processing="isStreaming"
                :disabled="isSendDisabled"
            >
                Send
            </AppButton>
        </div>
    </form>
</template>
