<script setup>
import { ref, toRef } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useAdminBrand } from '@/Composables/useAdminBrand.js';
import { useModalFocus } from '@/Composables/useModalFocus.js';
import AdminIcon from './AdminIcon.vue';

const props = defineProps({
    open: { type: Boolean, default: false },
    navigation: { type: Array, default: () => [] },
    site: { type: Object, default: () => ({}) },
});
const emit = defineEmits(['close']);
const drawer = ref(null);
const { siteName, initial } = useAdminBrand(toRef(props, 'site'));
const { onKeydown } = useModalFocus({
    open: toRef(props, 'open'),
    container: drawer,
    onEscape: () => emit('close'),
});

const isActive = (item) => (item.active_patterns || []).some((pattern) => route().current(pattern));
</script>

<template>
    <Teleport to="body">
        <div v-if="open" class="fixed inset-0 z-50 lg:hidden">
            <button type="button" class="absolute inset-0 h-full w-full bg-black/70" aria-label="Đóng menu quản trị" @click="emit('close')" />
            <aside ref="drawer" tabindex="-1" role="dialog" aria-modal="true" aria-label="Menu quản trị trên thiết bị di động" class="relative flex h-full w-[min(86vw,21rem)] max-w-full flex-col bg-admin-surface shadow-2xl focus:outline-none" @keydown="onKeydown">
                <div class="flex min-h-16 items-center justify-between border-b border-admin-border px-4">
                    <div class="flex min-w-0 items-center gap-3">
                        <img v-if="site.logo_url" :src="site.logo_url" :alt="siteName" class="h-9 w-9 rounded-lg object-contain" />
                        <span v-else class="flex h-9 w-9 items-center justify-center rounded-lg bg-admin-accent font-bold text-admin-page">{{ initial }}</span>
                        <span class="truncate text-sm font-bold text-admin-content" :title="siteName">{{ siteName }}</span>
                    </div>
                    <button type="button" class="rounded-lg p-2 text-admin-content-muted hover:bg-admin-surface-muted hover:text-admin-content focus:outline-none focus:ring-2 focus:ring-admin-focus" aria-label="Đóng menu quản trị" @click="emit('close')"><AdminIcon name="close" /></button>
                </div>
                <nav class="flex-1 overflow-y-auto px-3 py-4" aria-label="Điều hướng quản trị trên thiết bị di động">
                    <Link v-for="item in navigation" :key="item.key" :href="route(item.route)" :aria-current="isActive(item) ? 'page' : undefined" :class="['mb-1 flex items-center gap-3 rounded-lg border px-3 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-admin-focus', isActive(item) ? 'border-admin-accent/20 bg-admin-accent/10 text-admin-accent' : 'border-transparent text-admin-content-muted hover:bg-admin-surface-muted hover:text-admin-content']" @click="emit('close')">
                        <AdminIcon :name="item.icon" />
                        <span class="truncate">{{ item.label }}</span>
                    </Link>
                </nav>
            </aside>
        </div>
    </Teleport>
</template>
