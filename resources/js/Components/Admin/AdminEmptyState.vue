<script setup>
defineProps({ message: { type: String, default: 'Chưa có dữ liệu.' } });
</script>

<template><p class="py-8 text-center text-sm text-carbon-400">{{ message }}</p></template>
