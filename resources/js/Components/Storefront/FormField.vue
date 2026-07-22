<script setup>
defineProps({
    id: { type: String, required: true },
    label: { type: String, required: true },
    hint: { type: String, default: '' },
    error: { type: String, default: '' },
    required: { type: Boolean, default: false },
});
</script>

<template>
    <div>
        <label :for="id" class="mb-2 block text-sm font-semibold text-content-primary">
            {{ label }} <span v-if="required" class="text-danger" aria-hidden="true">*</span>
        </label>
        <slot :id="id" :described-by="error ? `${id}-error` : hint ? `${id}-hint` : undefined" />
        <p v-if="error" :id="`${id}-error`" class="mt-1.5 text-sm text-danger">{{ error }}</p>
        <p v-else-if="hint" :id="`${id}-hint`" class="mt-1.5 text-sm text-content-muted">{{ hint }}</p>
    </div>
</template>
