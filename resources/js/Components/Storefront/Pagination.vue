<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({ links: { type: Array, default: () => [] } });
</script>

<template>
    <nav v-if="links.length > 3" aria-label="Phân trang" class="flex flex-wrap justify-center gap-2">
        <component
            :is="link.url ? Link : 'span'"
            v-for="(link, index) in links"
            :key="`${link.label}-${index}`"
            :href="link.url || undefined"
            class="inline-flex min-h-10 min-w-10 items-center justify-center rounded-lg border px-3 text-sm"
            :class="link.active ? 'border-brand bg-brand text-brand-contrast' : link.url ? 'border-line bg-surface-card hover:border-brand-border' : 'border-line-subtle text-content-muted opacity-60'"
            v-html="link.label"
        />
    </nav>
</template>
