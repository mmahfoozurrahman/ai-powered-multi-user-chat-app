<script setup>
const props = defineProps({
    code: {
        type: String,
        default: '',
    },
    language: {
        type: String,
        default: 'code',
    },
});

const copyCode = async () => {
    if (!navigator?.clipboard) {
        return;
    }

    await navigator.clipboard.writeText(props.code);
};
</script>

<template>
    <div class="code-block">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <small class="text-white-50">{{ language || 'code' }}</small>
            <button type="button" class="btn btn-sm btn-outline-light" @click="copyCode">
                Copy
            </button>
        </div>
        <pre class="mb-0"><code>{{ code }}</code></pre>
    </div>
</template>
