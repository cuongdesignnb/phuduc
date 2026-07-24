<script setup>
import { nextTick, ref, watch } from 'vue';
import { Link } from '@inertiajs/vue3';
import AdminIcon from './AdminIcon.vue';

const props = defineProps({
    open: { type: Boolean, default: false },
    navigation: { type: Array, default: () => [] },
    site: { type: Object, default: () => ({}) },
});
const emit = defineEmits(['close']);
const drawer = ref(null);
const isActive = (item) => (item.active_patterns || []).some((pattern) => route().current(pattern));

watch(() => props.open, async (open) => {
    if (open) {
        await nextTick();
        drawer.value?.focus();
    }
});

const close = () => emit('close');
const onKeydown = (event) => {
    if (event.key === 'Escape') close();
};
</script>

<template>
    <Teleport to="body">
        <div v-if="open" class="fixed inset-0 z-50 lg:hidden" @keydown="onKeydown">
            <button type="button" class="absolute inset-0 h-full w-full bg-black/70" aria-label="Đóng menu quản trị" @click="close" />
            <aside ref="drawer" tabindex="-1" role="dialog" aria-modal="true" aria-label="Menu quản trị trên thiết bị di động" class="relative flex h-full w-[min(86vw,21rem)] max-w-full flex-col bg-carbon-900 shadow-2xl focus:outline-none">
                <div class="flex min-h-16 items-center justify-between border-b border-white/10 px-4">
                    <div class="flex min-w-0 items-center gap-3">
                        <img v-if="site.logo_url" :src="site.logo_url" :alt="site.name || 'Logo'" class="h-9 w-9 rounded-lg object-contain" />
                        <span v-else class="flex h-9 w-9 items-center justify-center rounded-lg bg-volt-500 font-bold text-carbon-950">P</span>
                        <span class="truncate text-sm font-bold text-white">{{ site.name || 'Quản trị' }}</span>
                    </div>
                    <button type="button" class="rounded-lg p-2 text-carbon-300 hover:bg-white/10 hover:text-white focus:outline-none focus:ring-2 focus:ring-volt-400" aria-label="Đóng menu quản trị" @click="close"><AdminIcon name="close" /></button>
                </div>
                <nav class="flex-1 overflow-y-auto px-3 py-4" aria-label="Điều hướng quản trị trên thiết bị di động">
                    <Link v-for="item in navigation" :key="item.key" :href="route(item.route)" :aria-current="isActive(item) ? 'page' : undefined" :class="['mb-1 flex items-center gap-3 rounded-lg border px-3 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-volt-400', isActive(item) ? 'border-volt-400/20 bg-volt-400/10 text-volt-300' : 'border-transparent text-carbon-300 hover:bg-white/10 hover:text-white']" @click="close">
                        <AdminIcon :name="item.icon" />
                        <span class="truncate">{{ item.label }}</span>
                    </Link>
                </nav>
            </aside>
        </div>
    </Teleport>
</template>
