<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({ items: { type: Array, default: () => [] } });
</script>

<template>
    <nav aria-label="Breadcrumb" class="text-sm text-content-muted">
        <ol class="flex flex-wrap items-center gap-2">
            <li v-for="(item, index) in items" :key="`${item.label}-${index}`" class="flex items-center gap-2">
                <span v-if="index" aria-hidden="true">/</span>
                <Link v-if="item.href && index < items.length - 1" :href="item.href" class="hover:text-brand-hover">{{ item.label }}</Link>
                <span v-else :aria-current="index === items.length - 1 ? 'page' : undefined">{{ item.label }}</span>
            </li>
        </ol>
    </nav>
</template>
