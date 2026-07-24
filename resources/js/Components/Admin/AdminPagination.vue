<script setup>
import { Link } from '@inertiajs/vue3';
defineProps({ links: { type: Array, default: () => [] } });
</script>

<template>
    <nav v-if="links.length" aria-label="Phân trang" class="flex flex-wrap gap-2">
        <Link v-for="(link, index) in links" :key="index" :href="link.url || '#'" :class="['rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-volt-400', link.active ? 'border-volt-400 text-volt-300' : 'border-white/10 text-carbon-300']" v-html="link.label" />
    </nav>
</template>
