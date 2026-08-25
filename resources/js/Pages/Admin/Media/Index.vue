<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AdminConfirmDialog from '@/Components/Admin/AdminConfirmDialog.vue';
import AdminDataCard from '@/Components/Admin/AdminDataCard.vue';
import AdminFormField from '@/Components/Admin/AdminFormField.vue';
import AdminPageHeader from '@/Components/Admin/AdminPageHeader.vue';
import AdminPagination from '@/Components/Admin/AdminPagination.vue';
import AdminTextInput from '@/Components/Admin/AdminTextInput.vue';
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, ref, watch } from 'vue';

const props = defineProps({ page: { type: Object, required: true } });
const module = computed(() => props.page.module);
const search = ref(module.value.filters.search || '');
const mediaType = ref(module.value.filters.media_type || '');
const currentFolderId = ref(module.value.filters.folder_id || null);
const deleting = ref(null);
const alt = ref('');
const selectedIds = ref([]);
const moveFolderId = ref('');
const newFolderName = ref('');
const creatingFolder = ref(false);
let timer;
const folders = computed(() => module.value.folders || []);
const folderLabel = (folder) => `${'— '.repeat(folder.depth || 0)}${folder.name}`;
const refresh = () => router.get(route('admin.media.index'), { search: search.value || undefined, media_type: mediaType.value || undefined, folder_id: currentFolderId.value || undefined }, { preserveState: false, replace: true });
watch([search, mediaType], () => { clearTimeout(timer); timer = setTimeout(refresh, 300); });
watch(() => module.value.filters.folder_id, (value) => { currentFolderId.value = value || null; });
const selectFolder = () => { selectedIds.value = []; refresh(); };
const toggleMedia = (id) => { selectedIds.value = selectedIds.value.includes(id) ? selectedIds.value.filter((value) => value !== id) : [...selectedIds.value, id]; };
const upload = (event) => { const files = Array.from(event.target.files || []); if (!files.length) return; const data = new FormData(); files.forEach((file) => data.append('files[]', file)); if (currentFolderId.value) data.append('folder_id', currentFolderId.value); router.post(route('admin.media.store'), data, { forceFormData: true, preserveScroll: true, onFinish: () => { event.target.value = ''; } }); };
const createFolder = async () => { const name = newFolderName.value.trim(); if (!name) return; await axios.post(route('admin.media.folders.store'), { name, parent_id: currentFolderId.value || null }); newFolderName.value = ''; creatingFolder.value = false; refresh(); };
const moveSelected = async () => { if (!selectedIds.value.length) return; await axios.post(route('admin.media.move'), { media_ids: selectedIds.value, folder_id: moveFolderId.value || null }); selectedIds.value = []; moveFolderId.value = ''; refresh(); };
const editAlt = (media) => { const data = new FormData(); data.append('_method', 'PATCH'); data.append('alt_text', media.alt_text || ''); router.post(media.edit_url, data, { forceFormData: true, preserveScroll: true }); };
const requestDelete = (media) => { deleting.value = media; };
const destroy = () => { router.delete(deleting.value.delete_url, { preserveScroll: true, onFinish: () => { deleting.value = null; } }); };
</script>

<template>
    <Head :title="page.meta.title" />
    <AuthenticatedLayout>
        <AdminPageHeader :title="page.meta.title" description="Tệp dùng chung cho catalog và nội dung" />
        <AdminDataCard title="Tải Media" class="mt-6">
            <AdminFormField label="Tải nhiều tệp vào thư mục đang mở" for-id="media-files" hint="JPEG, PNG, WebP, GIF, MP4, WebM hoặc PDF; ảnh JPEG/PNG sẽ được tối ưu thành WebP."><div class="flex flex-wrap items-center gap-3"><label class="cursor-pointer rounded bg-admin-accent px-4 py-2 text-sm font-semibold text-admin-page"><input id="media-files" type="file" multiple accept="image/jpeg,image/png,image/webp,image/gif,video/mp4,video/webm,application/pdf" class="sr-only" @change="upload" />Chọn tệp tải lên</label><span class="text-sm text-admin-content-muted">{{ currentFolderId ? 'Tệp sẽ vào thư mục đang chọn.' : 'Tệp sẽ vào thư mục gốc.' }}</span></div></AdminFormField>
        </AdminDataCard>
        <AdminDataCard title="Thư mục Media" class="mt-6"><div class="flex flex-wrap items-center gap-3"><select v-model="currentFolderId" class="min-w-60 border border-admin-border bg-admin-page px-3 py-2 text-sm text-admin-content" aria-label="Chọn thư mục Media" @change="selectFolder"><option :value="null">Tất cả Media</option><option v-for="folder in folders" :key="folder.id" :value="folder.id">{{ folderLabel(folder) }} ({{ folder.media_count }})</option></select><button type="button" class="rounded border border-admin-border px-3 py-2 text-sm text-admin-content" @click="creatingFolder = !creatingFolder">Tạo thư mục</button></div><div v-if="creatingFolder" class="mt-3 flex max-w-md gap-2"><AdminTextInput v-model="newFolderName" placeholder="Tên thư mục mới" aria-label="Tên thư mục mới" @keyup.enter="createFolder" /><button type="button" class="rounded bg-admin-accent px-3 py-2 text-sm font-semibold text-admin-page" @click="createFolder">Tạo</button></div></AdminDataCard>
        <AdminDataCard title="Thư viện Media" class="mt-6"><div class="mb-4 grid gap-3 sm:grid-cols-[minmax(0,1fr)_12rem]"><AdminTextInput v-model="search" aria-label="Tìm Media" placeholder="Tìm tên tệp hoặc alt text" /><select v-model="mediaType" class="border border-admin-border bg-admin-page px-3 py-2 text-sm text-admin-content" aria-label="Lọc loại Media"><option value="">Tất cả loại</option><option value="image">Hình ảnh</option><option value="file">Tệp khác</option></select></div><div v-if="selectedIds.length" class="mb-4 flex flex-wrap items-center gap-2 border border-admin-accent/30 bg-admin-accent/5 p-3"><span class="text-sm text-admin-content">Đã chọn {{ selectedIds.length }} tệp</span><select v-model="moveFolderId" class="border border-admin-border bg-admin-page px-3 py-2 text-sm text-admin-content" aria-label="Chuyển tệp sang thư mục"><option value="">Thư mục gốc</option><option v-for="folder in folders" :key="folder.id" :value="folder.id">{{ folderLabel(folder) }}</option></select><button type="button" class="rounded bg-admin-accent px-3 py-2 text-sm font-semibold text-admin-page" @click="moveSelected">Chuyển thư mục</button></div><div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4"><article v-for="media in module.items" :key="media.id" class="relative border border-admin-border bg-admin-page p-3"><label class="absolute left-5 top-5 z-10 rounded bg-admin-page/90 p-1"><input type="checkbox" :checked="selectedIds.includes(media.id)" :aria-label="`Chọn ${media.file_name}`" @change="toggleMedia(media.id)" /></label><img v-if="media.thumbnail_url" :src="media.thumbnail_url" :alt="media.alt_text || media.file_name" class="aspect-video w-full object-cover" loading="lazy" /><div v-else class="grid aspect-video place-items-center bg-admin-surface-muted text-xs text-admin-content-muted">{{ media.mime_type }}</div><p class="mt-2 truncate text-sm text-admin-content">{{ media.file_name }}</p><p class="text-xs text-admin-content-muted">{{ media.size_display }} · {{ media.created_at_display }}</p><input v-model="media.alt_text" type="text" class="mt-2 w-full border border-admin-border bg-admin-surface px-2 py-1 text-xs text-admin-content" aria-label="Alt text" @change="alt = media.alt_text; editAlt(media)" /><div class="mt-3 flex items-center justify-between gap-2"><span class="text-xs text-admin-content-muted">{{ media.references_count }} tham chiếu</span><button v-if="media.can_delete" type="button" class="text-xs text-admin-danger" @click="requestDelete(media)">Xóa</button><span v-else class="text-xs text-admin-content-muted">Đang dùng</span></div></article></div><p v-if="!module.items.length" class="py-10 text-center text-sm text-admin-content-muted">Thư mục này chưa có Media.</p><AdminPagination class="mt-5" :pagination="module.pagination" /></AdminDataCard>
        <AdminConfirmDialog :open="!!deleting" title="Xóa Media" message="Media không có tham chiếu sẽ bị xóa khỏi thư viện và storage." confirm-label="Xóa Media" danger @cancel="deleting = null" @confirm="destroy" />
    </AuthenticatedLayout>
</template>
