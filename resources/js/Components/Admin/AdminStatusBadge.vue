<script setup>
defineProps({ status: { type: Object, default: () => ({}) } });
</script>

<template><span class="inline-flex max-w-full items-center rounded-full border border-white/15 px-2 py-1 text-xs text-carbon-200">{{ status.label || status.key || 'Không rõ' }}</span></template>
