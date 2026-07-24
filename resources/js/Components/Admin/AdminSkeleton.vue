<script setup>
defineProps({ rows: { type: Number, default: 3 } });
</script>

<template><div class="space-y-3" aria-busy="true" aria-label="Đang tải"><div v-for="row in rows" :key="row" class="h-4 animate-pulse rounded bg-carbon-800" /></div></template>
