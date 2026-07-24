<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AdminConfirmDialog from '@/Components/Admin/AdminConfirmDialog.vue';
import AdminDataCard from '@/Components/Admin/AdminDataCard.vue';
import AdminFormField from '@/Components/Admin/AdminFormField.vue';
import AdminPageHeader from '@/Components/Admin/AdminPageHeader.vue';
import AdminPagination from '@/Components/Admin/AdminPagination.vue';
import AdminTextInput from '@/Components/Admin/AdminTextInput.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({ page: { type: Object, required: true } });
const module = props.page.module;
const search = ref(module.filters.search || '');
const mediaType = ref(module.filters.media_type || '');
const deleting = ref(null);
const alt = ref('');
let timer;
watch([search, mediaType], () => { clearTimeout(timer); timer = setTimeout(() => router.get(route('admin.media.index'), { search: search.value || undefined, media_type: mediaType.value || undefined }, { preserveState: true, replace: true }), 300); });
const upload = (event) => { const files = Array.from(event.target.files); if (!files.length) return; const data = new FormData(); files.forEach((file) => data.append('files[]', file)); router.post(route('admin.media.store'), data, { forceFormData: true, preserveScroll: true, onFinish: () => { event.target.value = ''; } }); };
const editAlt = (media) => { const data = new FormData(); data.append('_method', 'PATCH'); data.append('alt_text', alt.value); router.post(media.edit_url, data, { forceFormData: true, preserveScroll: true }); };
const requestDelete = (media) => { deleting.value = media; };
const destroy = () => { router.delete(deleting.value.delete_url, { preserveScroll: true, onFinish: () => { deleting.value = null; } }); };
</script>

<template>
    <Head :title="page.meta.title" />
    <AuthenticatedLayout>
        <AdminPageHeader :title="page.meta.title" description="Tệp dùng chung cho catalog và nội dung" />
        <AdminDataCard title="Tải Media" class="mt-6"><AdminFormField label="Chọn tối đa 20 tệp, mỗi tệp tối đa 10 MB" for-id="media-files" hint="JPEG, PNG, WebP, GIF, MP4, WebM hoặc PDF; tổng request tối đa 50 MB."><input id="media-files" type="file" multiple accept="image/jpeg,image/png,image/webp,image/gif,video/mp4,video/webm,application/pdf" class="block w-full text-sm text-admin-content" @change="upload" /></AdminFormField></AdminDataCard>
        <AdminDataCard title="Thư viện Media" class="mt-6"><div class="mb-4 grid gap-3 sm:grid-cols-2"><AdminTextInput v-model="search" aria-label="Tìm Media" placeholder="Tìm tên tệp hoặc alt text" /><select v-model="mediaType" class="border border-admin-border bg-admin-page px-3 py-2 text-sm text-admin-content" aria-label="Lọc loại Media"><option value="">Tất cả loại</option><option value="image">Hình ảnh</option><option value="file">Tệp khác</option></select></div><div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4"><article v-for="media in module.items" :key="media.id" class="border border-admin-border bg-admin-page p-3"><img v-if="media.thumbnail_url" :src="media.thumbnail_url" :alt="media.alt_text || media.file_name" class="aspect-video w-full object-cover" loading="lazy" /><div v-else class="grid aspect-video place-items-center bg-admin-surface-muted text-xs text-admin-content-muted">{{ media.mime_type }}</div><p class="mt-2 truncate text-sm text-admin-content">{{ media.file_name }}</p><p class="text-xs text-admin-content-muted">{{ media.size_display }} · {{ media.created_at_display }}</p><input v-model="media.alt_text" type="text" class="mt-2 w-full border border-admin-border bg-admin-surface px-2 py-1 text-xs text-admin-content" aria-label="Alt text" @change="alt = media.alt_text; editAlt(media)" /><div class="mt-3 flex items-center justify-between gap-2"><span class="text-xs text-admin-content-muted">{{ media.references_count }} tham chiếu</span><button v-if="media.can_delete" type="button" class="text-xs text-admin-danger" @click="requestDelete(media)">Xóa</button><span v-else class="text-xs text-admin-content-muted">Đang dùng</span></div></article></div><p v-if="!module.items.length" class="py-10 text-center text-sm text-admin-content-muted">Chưa có Media.</p><AdminPagination class="mt-5" :pagination="module.pagination" /></AdminDataCard>
        <AdminConfirmDialog :open="!!deleting" title="Xóa Media" message="Media không có tham chiếu sẽ bị xóa khỏi thư viện và storage." confirm-label="Xóa Media" danger @cancel="deleting = null" @confirm="destroy" />
    </AuthenticatedLayout>
</template>
