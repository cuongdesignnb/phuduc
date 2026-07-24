<script setup>
defineProps({
    label: { type: String, required: true },
    value: { type: [String, Number], required: true },
    tone: { type: String, default: 'volt' },
});
</script>

<template>
    <article class="border border-white/10 bg-carbon-900 p-4">
        <p class="text-sm text-carbon-400">{{ label }}</p>
        <p :class="tone === 'amber' ? 'text-amber-300' : 'text-volt-300'" class="mt-2 break-words text-2xl font-bold">{{ value }}</p>
    </article>
</template>
