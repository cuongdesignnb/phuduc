<script setup>
import { Link } from '@inertiajs/vue3';
import AdminIcon from './AdminIcon.vue';

defineProps({
    navigation: { type: Array, default: () => [] },
    site: { type: Object, default: () => ({}) },
    collapsed: { type: Boolean, default: false },
});

const isActive = (item) => (item.active_patterns || []).some((pattern) => route().current(pattern));
</script>

<template>
    <aside class="flex h-full flex-col bg-carbon-900" :aria-label="collapsed ? 'Menu quản trị thu gọn' : 'Menu quản trị'">
        <div class="flex min-h-16 items-center gap-3 border-b border-white/10 px-4">
            <Link :href="route('dashboard')" class="flex min-w-0 items-center gap-3 focus:outline-none focus:ring-2 focus:ring-volt-400" :aria-label="site.name || 'Trang quản trị'">
                <img v-if="site.logo_url" :src="site.logo_url" :alt="site.name || 'Logo'" class="h-9 w-9 shrink-0 rounded-lg object-contain" />
                <span v-else class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-volt-500 text-lg font-bold text-carbon-950">P</span>
                <span v-if="!collapsed" class="truncate text-sm font-bold text-white">{{ site.name || 'Quản trị' }}</span>
            </Link>
        </div>
        <nav class="flex-1 overflow-y-auto px-3 py-4" aria-label="Điều hướng quản trị">
            <Link v-for="item in navigation" :key="item.key" :href="route(item.route)" :title="collapsed ? item.label : undefined" :aria-current="isActive(item) ? 'page' : undefined" :class="['mb-1 flex items-center gap-3 rounded-lg border px-3 py-2.5 text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-volt-400', isActive(item) ? 'border-volt-400/20 bg-volt-400/10 text-volt-300' : 'border-transparent text-carbon-300 hover:bg-white/10 hover:text-white']">
                <AdminIcon :name="item.icon" class="shrink-0" />
                <span v-if="!collapsed" class="truncate">{{ item.label }}</span>
            </Link>
        </nav>
    </aside>
</template>
