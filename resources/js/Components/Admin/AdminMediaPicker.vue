<script setup>
import { ref, toRef, watch } from 'vue';
import axios from 'axios';
import AdminTextInput from '@/Components/Admin/AdminTextInput.vue';
import { useModalFocus } from '@/Composables/useModalFocus.js';

const props = defineProps({ open: { type: Boolean, default: false }, items: { type: Array, default: () => [] }, selectedId: { type: [Number, String], default: null }, selectedIds: { type: Array, default: () => [] }, title: { type: String, default: 'Chọn tệp' }, mediaType: { type: String, default: null } });
const emit = defineEmits(['select', 'close']);
const panel = ref(null); const search = ref(''); const page = ref(1); const loading = ref(false); const remoteItems = ref(props.items); const pagination = ref({});
const { onKeydown } = useModalFocus({ open: toRef(props, 'open'), container: panel, onEscape: () => emit('close') });
const load = async () => { loading.value = true; try { const response = await axios.get(route('admin.media.data'), { params: { search: search.value || undefined, media_type: props.mediaType || undefined, page: page.value, limit: 20, ids: props.selectedIds.length ? props.selectedIds : (props.selectedId ? [props.selectedId] : undefined) } }); remoteItems.value = response.data.items || response.data.data || []; pagination.value = response.data.pagination || {}; } finally { loading.value = false; } };
let timer;
watch(() => props.open, (open) => { if (open) { page.value = 1; load(); } });
watch(search, () => { clearTimeout(timer); timer = setTimeout(() => { page.value = 1; load(); }, 250); });
const nextPage = () => { if (pagination.value.current_page < pagination.value.last_page) { page.value += 1; load(); } };
const previousPage = () => { if (page.value > 1) { page.value -= 1; load(); } };
</script>

<template>
    <Teleport to="body"><div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4" role="dialog" aria-modal="true" aria-label="Chọn tệp" @keydown="onKeydown"><div ref="panel" tabindex="-1" class="max-h-[80vh] w-full max-w-4xl overflow-y-auto border border-admin-border bg-admin-surface p-5 focus:outline-none"><div class="flex items-center justify-between gap-3"><h2 class="text-lg font-semibold text-admin-content">{{ title }}</h2><button type="button" class="p-2 text-admin-content-muted" aria-label="Đóng bộ chọn tệp" @click="emit('close')">×</button></div><AdminTextInput v-model="search" class="mt-4" placeholder="Tìm tệp" aria-label="Tìm tệp" /><div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4"><button v-for="media in remoteItems" :key="media.id" type="button" class="border p-2 text-left focus:outline-none focus:ring-2 focus:ring-admin-focus" :class="media.id === selectedId || selectedIds.includes(media.id) ? 'border-admin-accent bg-admin-accent/10' : 'border-admin-border'" @click="emit('select', media)"><img v-if="media.thumbnail_url" :src="media.thumbnail_url" :alt="media.alt_text || media.file_name" class="aspect-square w-full object-cover" loading="lazy" /><span v-else class="grid aspect-square place-items-center bg-admin-surface-muted text-xs text-admin-content-muted">{{ media.mime_type }}</span><span class="mt-2 block truncate text-xs text-admin-content">{{ media.file_name }}</span></button></div><p v-if="loading" class="py-4 text-center text-sm text-admin-content-muted">Đang tải...</p><p v-else-if="!remoteItems.length" class="py-8 text-center text-sm text-admin-content-muted">Không tìm thấy tệp.</p><div v-if="pagination.last_page > 1" class="mt-4 flex justify-between"><button type="button" :disabled="page <= 1" class="text-sm text-admin-accent disabled:opacity-50" @click="previousPage">Trước</button><span class="text-sm text-admin-content-muted">{{ page }} / {{ pagination.last_page }}</span><button type="button" :disabled="page >= pagination.last_page" class="text-sm text-admin-accent disabled:opacity-50" @click="nextPage">Sau</button></div></div></div></Teleport>
</template>
