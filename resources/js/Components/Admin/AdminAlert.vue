<script setup>
defineProps({ message: { type: String, default: '' }, tone: { type: String, default: 'info' } });
</script>

<template><div v-if="message" role="status" :class="tone === 'error' ? 'border-red-400/30 text-red-200' : 'border-volt-400/30 text-volt-200'" class="border px-4 py-3 text-sm">{{ message }}</div></template>
