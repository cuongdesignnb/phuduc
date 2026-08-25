<script setup>
import { computed, ref, toRef, watch } from 'vue';
import axios from 'axios';
import AdminTextInput from '@/Components/Admin/AdminTextInput.vue';
import { useModalFocus } from '@/Composables/useModalFocus.js';

const props = defineProps({
    open: { type: Boolean, default: false },
    items: { type: Array, default: () => [] },
    selectedId: { type: [Number, String], default: null },
    selectedIds: { type: Array, default: () => [] },
    title: { type: String, default: 'Thư viện Media' },
    mediaType: { type: String, default: null },
    multiple: { type: Boolean, default: false },
});
const emit = defineEmits(['select', 'close']);
const panel = ref(null);
const search = ref('');
const page = ref(1);
const loading = ref(false);
const uploading = ref(false);
const creatingFolder = ref(false);
const newFolderName = ref('');
const remoteItems = ref(props.items);
const folders = ref([]);
const currentFolderId = ref(null);
const selected = ref(new Set());
const selectedItems = ref(new Map());
const pagination = ref({});
const { onKeydown } = useModalFocus({ open: toRef(props, 'open'), container: panel, onEscape: () => emit('close') });

const selectedCount = computed(() => selected.value.size);
const folderName = (folder) => `${'— '.repeat(folder.depth || 0)}${folder.name}`;
const isSelected = (media) => selected.value.has(Number(media.id));
const syncSelected = () => {
    const ids = props.selectedIds.length ? props.selectedIds : (props.selectedId ? [props.selectedId] : []);
    selected.value = new Set(ids.map((id) => Number(id)));
    selectedItems.value = new Map();
};
const load = async () => {
    loading.value = true;
    try {
        const response = await axios.get(route('admin.media.data'), { params: { search: search.value || undefined, media_type: props.mediaType || undefined, folder_id: currentFolderId.value || undefined, page: page.value, limit: 20, ids: selected.value.size ? [...selected.value] : undefined } });
        remoteItems.value = response.data.items || response.data.data || [];
        remoteItems.value.forEach((media) => { if (selected.value.has(Number(media.id))) selectedItems.value.set(Number(media.id), media); });
        folders.value = response.data.folders || [];
        pagination.value = response.data.pagination || {};
    } finally {
        loading.value = false;
    }
};
const choose = (media) => {
    if (props.multiple) {
        const next = new Set(selected.value);
        const id = Number(media.id);
        if (next.has(id)) next.delete(id); else next.add(id);
        selected.value = next;
        if (next.has(id)) selectedItems.value.set(id, media); else selectedItems.value.delete(id);
        return;
    }
    emit('select', media);
    emit('close');
};
const confirm = () => {
    const items = [...selected.value].map((id) => selectedItems.value.get(id)).filter(Boolean);
    if (items.length !== selected.value.size) {
        load();
        return;
    }
    emit('select', items);
    emit('close');
};
const upload = async (event) => {
    const files = Array.from(event.target.files || []);
    if (!files.length) return;
    const data = new FormData();
    files.forEach((file) => data.append('files[]', file));
    if (currentFolderId.value) data.append('folder_id', currentFolderId.value);
    uploading.value = true;
    try {
        const response = await axios.post(route('admin.media.store'), data, { headers: { Accept: 'application/json' } });
        const created = response.data.items || response.data.data || [];
        remoteItems.value = [...created, ...remoteItems.value];
        created.forEach((media) => { selected.value.add(Number(media.id)); selectedItems.value.set(Number(media.id), media); });
    } finally {
        uploading.value = false;
        event.target.value = '';
    }
};
const createFolder = async () => {
    const name = newFolderName.value.trim();
    if (!name) return;
    const response = await axios.post(route('admin.media.folders.store'), { name, parent_id: currentFolderId.value || null });
    folders.value = [...folders.value, response.data.folder].sort((a, b) => a.name.localeCompare(b.name));
    newFolderName.value = '';
    creatingFolder.value = false;
};
const selectFolder = (folderId) => {
    currentFolderId.value = folderId ? Number(folderId) : null;
    page.value = 1;
    load();
};
const nextPage = () => { if (pagination.value.current_page < pagination.value.last_page) { page.value += 1; load(); } };
const previousPage = () => { if (page.value > 1) { page.value -= 1; load(); } };
let timer;
watch(() => props.open, (open) => { if (open) { page.value = 1; currentFolderId.value = null; syncSelected(); load(); } });
watch(search, () => { clearTimeout(timer); timer = setTimeout(() => { page.value = 1; load(); }, 250); });
watch(() => props.selectedId, syncSelected);
watch(() => props.selectedIds, syncSelected, { deep: true });
</script>

<template>
    <Teleport to="body">
        <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4" role="dialog" aria-modal="true" :aria-label="title" @keydown="onKeydown">
            <div ref="panel" tabindex="-1" class="flex max-h-[88vh] w-full max-w-6xl flex-col overflow-hidden border border-admin-border bg-admin-surface focus:outline-none">
                <div class="flex items-center justify-between gap-3 border-b border-admin-border p-5"><div><h2 class="text-lg font-semibold text-admin-content">{{ title }}</h2><p class="text-xs text-admin-content-muted">{{ multiple ? 'Chọn một hoặc nhiều ảnh rồi xác nhận.' : 'Chọn ảnh từ thư viện hoặc tải ảnh mới lên.' }}</p></div><button type="button" class="p-2 text-admin-content-muted" aria-label="Đóng Thư viện Media" @click="emit('close')">×</button></div>
                <div class="grid min-h-0 flex-1 md:grid-cols-[13rem_1fr]">
                    <aside class="border-b border-admin-border bg-admin-page p-4 md:border-b-0 md:border-r"><div class="mb-3 flex items-center justify-between"><h3 class="text-sm font-semibold text-admin-content">Thư mục</h3><button type="button" class="text-xs text-admin-accent" @click="creatingFolder = !creatingFolder">Tạo mới</button></div><div v-if="creatingFolder" class="mb-3 space-y-2"><AdminTextInput v-model="newFolderName" placeholder="Tên thư mục" aria-label="Tên thư mục mới" @keyup.enter="createFolder" /><button type="button" class="w-full rounded bg-admin-accent px-3 py-2 text-xs font-semibold text-admin-page" @click="createFolder">Tạo thư mục</button></div><div class="max-h-[45vh] space-y-1 overflow-y-auto"><button type="button" class="block w-full rounded px-2 py-2 text-left text-sm" :class="currentFolderId === null ? 'bg-admin-accent/10 text-admin-content' : 'text-admin-content-muted'" @click="selectFolder(null)">Tất cả Media</button><button v-for="folder in folders" :key="folder.id" type="button" class="block w-full rounded px-2 py-2 text-left text-sm" :class="currentFolderId === folder.id ? 'bg-admin-accent/10 text-admin-content' : 'text-admin-content-muted'" @click="selectFolder(folder.id)">{{ folderName(folder) }} <span class="text-xs">({{ folder.media_count }})</span></button></div></aside>
                    <section class="flex min-h-0 flex-col p-5"><div class="flex flex-wrap gap-2"><AdminTextInput v-model="search" class="min-w-[15rem] flex-1" placeholder="Tìm tên file hoặc alt text" aria-label="Tìm Media" /><label class="cursor-pointer rounded border border-admin-border px-3 py-2 text-sm text-admin-content"><input type="file" multiple class="sr-only" :accept="mediaType === 'image' ? 'image/jpeg,image/png,image/webp,image/gif' : undefined" :disabled="uploading" @change="upload" />{{ uploading ? 'Đang tải...' : 'Tải lên' }}</label></div><div class="mt-4 min-h-0 flex-1 overflow-y-auto"><div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5"><button v-for="media in remoteItems" :key="media.id" type="button" class="relative border p-2 text-left focus:outline-none focus:ring-2 focus:ring-admin-focus" :class="isSelected(media) ? 'border-admin-accent bg-admin-accent/10' : 'border-admin-border'" @click="choose(media)"><span v-if="multiple && isSelected(media)" class="absolute right-2 top-2 z-10 rounded-full bg-admin-accent px-2 py-1 text-xs font-bold text-admin-page">✓</span><img v-if="media.thumbnail_url" :src="media.thumbnail_url" :alt="media.alt_text || media.file_name" class="aspect-square w-full object-cover" loading="lazy" /><span v-else class="grid aspect-square place-items-center bg-admin-surface-muted text-xs text-admin-content-muted">{{ media.mime_type }}</span><span class="mt-2 block truncate text-xs text-admin-content">{{ media.file_name }}</span><span v-if="media.alt_text" class="block truncate text-[11px] text-admin-content-muted">{{ media.alt_text }}</span></button></div><p v-if="loading" class="py-8 text-center text-sm text-admin-content-muted">Đang tải...</p><p v-else-if="!remoteItems.length" class="py-8 text-center text-sm text-admin-content-muted">Thư mục này chưa có Media.</p></div><div v-if="pagination.last_page > 1" class="mt-4 flex items-center justify-between"><button type="button" :disabled="page <= 1" class="text-sm text-admin-accent disabled:opacity-50" @click="previousPage">Trước</button><span class="text-sm text-admin-content-muted">{{ page }} / {{ pagination.last_page }}</span><button type="button" :disabled="page >= pagination.last_page" class="text-sm text-admin-accent disabled:opacity-50" @click="nextPage">Sau</button></div></section>
                </div>
                <div v-if="multiple" class="flex items-center justify-between border-t border-admin-border p-4"><span class="text-sm text-admin-content-muted">Đã chọn {{ selectedCount }} ảnh</span><div class="flex gap-2"><button type="button" class="rounded border border-admin-border px-4 py-2 text-sm text-admin-content" @click="emit('close')">Hủy</button><button type="button" class="rounded bg-admin-accent px-4 py-2 text-sm font-semibold text-admin-page disabled:opacity-50" :disabled="selectedCount === 0" @click="confirm">Chọn ảnh</button></div></div>
            </div>
        </div>
    </Teleport>
</template>
