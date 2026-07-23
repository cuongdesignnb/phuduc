<script setup>
defineProps({
    html: { type: String, default: '' },
});
</script>

<template>
    <div
        v-if="html"
        class="prose max-w-none prose-headings:font-display prose-headings:text-content-primary prose-p:text-content-secondary prose-a:text-brand-text prose-img:rounded-lg"
        v-html="html"
    />
</template>
