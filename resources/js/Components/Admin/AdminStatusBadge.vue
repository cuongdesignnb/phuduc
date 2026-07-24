<script setup>
defineProps({ status: { type: Object, default: () => ({}) } });
</script>

<template><span class="inline-flex max-w-full items-center rounded-full border border-admin-border px-2 py-1 text-xs text-admin-content">{{ status.label || status.key || 'Không rõ' }}</span></template>
