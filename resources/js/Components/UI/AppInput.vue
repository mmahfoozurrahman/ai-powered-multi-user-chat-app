<script setup>
defineProps({
    id: {
        type: String,
        default: '',
    },
    name: {
        type: String,
        default: '',
    },
    label: {
        type: String,
        default: '',
    },
    modelValue: {
        type: [String, Number],
        default: '',
    },
    type: {
        type: String,
        default: 'text',
    },
    placeholder: {
        type: String,
        default: '',
    },
    autocomplete: {
        type: String,
        default: '',
    },
    error: {
        type: String,
        default: '',
    },
    help: {
        type: String,
        default: '',
    },
});

defineEmits(['update:modelValue']);
</script>

<template>
    <div>
        <label v-if="label" :for="id" class="form-label auth-form__label">{{ label }}</label>
        <input
            :id="id"
            :name="name || id"
            :type="type"
            :value="modelValue"
            :placeholder="placeholder"
            :autocomplete="autocomplete"
            class="form-control auth-form__input"
            :class="{ 'is-invalid': error }"
            @input="$emit('update:modelValue', $event.target.value)"
        >
        <div v-if="error" class="auth-form__error">
            <i class="bi bi-exclamation-circle me-2"></i>
            {{ error }}
        </div>
        <div v-else-if="help" class="auth-form__help">
            {{ help }}
        </div>
    </div>
</template>
