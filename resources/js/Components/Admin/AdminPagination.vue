<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    pagination: { type: Object, default: () => ({ links: [] }) },
});

const labelFor = (link) => {
    if (link.key === 'previous') return 'Trang trước';
    if (link.key === 'next') return 'Trang sau';

    return `Trang ${link.label}`;
};
</script>

<template>
    <nav v-if="pagination.links?.length" aria-label="Phân trang" class="flex flex-wrap items-center gap-2">
        <template v-for="link in pagination.links" :key="link.key">
            <span v-if="link.disabled || !link.url" :aria-label="labelFor(link)" class="rounded-lg border border-admin-border px-3 py-2 text-sm text-admin-content-muted opacity-60">{{ link.label }}</span>
            <Link v-else :href="link.url" :aria-current="link.active ? 'page' : undefined" :aria-label="labelFor(link)" :title="labelFor(link)" :class="['rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-admin-focus', link.active ? 'border-admin-accent text-admin-accent' : 'border-admin-border text-admin-content-muted hover:border-admin-accent hover:text-admin-content']">{{ link.label }}</Link>
        </template>
    </nav>
</template>
