<script setup>
import { ref, toRef } from 'vue';
import { useModalFocus } from '@/Composables/useModalFocus.js';

const props = defineProps({ open: { type: Boolean, default: false }, items: { type: Array, default: () => [] }, selectedId: { type: [Number, String], default: null }, title: { type: String, default: 'Chọn Media' } });
const emit = defineEmits(['select', 'close']);
const panel = ref(null);
const { onKeydown } = useModalFocus({ open: toRef(props, 'open'), container: panel, onEscape: () => emit('close') });
</script>

<template>
    <Teleport to="body">
        <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4" role="dialog" aria-modal="true" aria-label="Chọn Media" @keydown="onKeydown">
            <div ref="panel" tabindex="-1" class="max-h-[80vh] w-full max-w-4xl overflow-y-auto border border-admin-border bg-admin-surface p-5 focus:outline-none">
                <div class="flex items-center justify-between gap-3"><h2 class="text-lg font-semibold text-admin-content">{{ title }}</h2><button type="button" class="rounded-lg p-2 text-admin-content-muted hover:bg-admin-surface-muted hover:text-admin-content" aria-label="Đóng bộ chọn Media" @click="emit('close')">×</button></div>
                <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
                    <button v-for="media in items" :key="media.id" type="button" class="border p-2 text-left focus:outline-none focus:ring-2 focus:ring-admin-focus" :class="media.id === selectedId ? 'border-admin-accent bg-admin-accent/10' : 'border-admin-border'" @click="emit('select', media)">
                        <img v-if="media.thumbnail_url" :src="media.thumbnail_url" :alt="media.alt_text || media.file_name" class="aspect-square w-full object-cover" loading="lazy" />
                        <span v-else class="grid aspect-square place-items-center bg-admin-surface-muted text-xs text-admin-content-muted">{{ media.mime_type }}</span>
                        <span class="mt-2 block truncate text-xs text-admin-content">{{ media.file_name }}</span>
                    </button>
                </div>
                <p v-if="!items.length" class="py-8 text-center text-sm text-admin-content-muted">Chưa có Media phù hợp.</p>
            </div>
        </div>
    </Teleport>
</template>
