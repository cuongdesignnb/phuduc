<script setup>
defineProps({ title: { type: String, required: true } });
</script>

<template>
    <section class="min-w-0 border border-admin-border bg-admin-surface p-4 sm:p-5">
        <h2 class="mb-4 text-lg font-semibold text-admin-content">{{ title }}</h2>
        <slot />
    </section>
</template>
