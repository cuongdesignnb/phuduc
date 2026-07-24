<script setup>
defineProps({ title: { type: String, required: true } });
</script>

<template>
    <section class="min-w-0 border border-white/10 bg-carbon-900 p-4 sm:p-5">
        <h2 class="mb-4 text-lg font-semibold text-white">{{ title }}</h2>
        <slot />
    </section>
</template>
