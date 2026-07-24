<script setup>
defineProps({ label: { type: String, required: true }, error: { type: String, default: '' }, hint: { type: String, default: '' }, forId: { type: String, default: '' } });
</script>

<template>
    <div>
        <label v-if="forId" :for="forId" class="mb-1.5 block text-sm font-medium text-admin-content">{{ label }}</label>
        <span v-else class="mb-1.5 block text-sm font-medium text-admin-content">{{ label }}</span>
        <slot />
        <p v-if="hint && !error" class="mt-1 text-xs text-admin-content-muted">{{ hint }}</p>
        <p v-if="error" class="mt-1 text-sm text-admin-danger" role="alert">{{ error }}</p>
    </div>
</template>
