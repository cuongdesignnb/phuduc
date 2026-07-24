<script setup>
import { toRef } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useAdminBrand } from '@/Composables/useAdminBrand.js';
import AdminIcon from './AdminIcon.vue';

const props = defineProps({
    navigation: { type: Array, default: () => [] },
    site: { type: Object, default: () => ({}) },
    collapsed: { type: Boolean, default: false },
});
const { siteName, initial } = useAdminBrand(toRef(props, 'site'));
const isActive = (item) => (item.active_patterns || []).some((pattern) => route().current(pattern));
</script>

<template>
    <aside class="flex h-full flex-col bg-admin-surface" :aria-label="collapsed ? 'Menu quản trị thu gọn' : 'Menu quản trị'">
        <div class="flex min-h-16 items-center gap-3 border-b border-admin-border px-4">
            <Link :href="route('dashboard')" class="flex min-w-0 items-center gap-3 focus:outline-none focus:ring-2 focus:ring-admin-focus" :aria-label="siteName">
                <img v-if="site.logo_url" :src="site.logo_url" :alt="siteName" class="h-9 w-9 shrink-0 rounded-lg object-contain" />
                <span v-else class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-admin-accent text-lg font-bold text-admin-page">{{ initial }}</span>
                <span v-if="!collapsed" class="truncate text-sm font-bold text-admin-content" :title="siteName">{{ siteName }}</span>
            </Link>
        </div>
        <nav class="flex-1 overflow-y-auto px-3 py-4" aria-label="Điều hướng quản trị">
            <Link v-for="item in navigation" :key="item.key" :href="route(item.route)" :title="collapsed ? item.label : undefined" :aria-current="isActive(item) ? 'page' : undefined" :class="['mb-1 flex items-center gap-3 rounded-lg border px-3 py-2.5 text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-admin-focus', isActive(item) ? 'border-admin-accent/20 bg-admin-accent/10 text-admin-accent' : 'border-transparent text-admin-content-muted hover:bg-admin-surface-muted hover:text-admin-content']">
                <AdminIcon :name="item.icon" class="shrink-0" />
                <span v-if="!collapsed" class="truncate">{{ item.label }}</span>
            </Link>
        </nav>
    </aside>
</template>
