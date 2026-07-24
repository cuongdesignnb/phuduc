<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({ items: { type: Array, default: () => [] } });
</script>

<template>
    <nav v-if="items.length" aria-label="Đường dẫn trang" class="flex min-w-0 items-center gap-2 text-sm text-admin-content-muted">
        <template v-for="(item, index) in items" :key="`${item.label}-${index}`">
            <span v-if="index > 0" aria-hidden="true">/</span>
            <Link v-if="item.url && index < items.length - 1" :href="item.url" class="truncate hover:text-admin-content focus:outline-none focus:ring-2 focus:ring-admin-focus" :title="item.label">{{ item.label }}</Link>
            <span v-else class="truncate text-admin-content" aria-current="page" :title="item.label">{{ item.label }}</span>
        </template>
    </nav>
</template>
