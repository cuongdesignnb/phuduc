<script setup>
import { Link } from '@inertiajs/vue3';
defineProps({ items: { type: Array, default: () => [] } });
</script>

<template>
    <nav aria-label="Đường dẫn trang" class="flex min-w-0 items-center gap-2 text-sm text-carbon-400">
        <template v-for="(item, index) in items" :key="index">
            <span aria-hidden="true">/</span>
            <Link v-if="item.url" :href="item.url" class="truncate hover:text-white focus:outline-none focus:ring-2 focus:ring-volt-400">{{ item.label }}</Link>
            <span v-else class="truncate text-white" aria-current="page">{{ item.label }}</span>
        </template>
    </nav>
</template>
