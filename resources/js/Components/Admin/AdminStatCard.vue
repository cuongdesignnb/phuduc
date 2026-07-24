<script setup>
defineProps({
    label: { type: String, required: true },
    value: { type: [String, Number], required: true },
    tone: { type: String, default: 'accent' },
});
</script>

<template>
    <article class="border border-admin-border bg-admin-surface p-4">
        <p class="text-sm text-admin-content-muted">{{ label }}</p>
        <p :class="tone === 'warning' ? 'text-admin-warning' : 'text-admin-accent'" class="mt-2 break-words text-2xl font-bold">{{ value }}</p>
    </article>
</template>
