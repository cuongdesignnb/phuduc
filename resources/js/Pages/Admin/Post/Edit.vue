<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AdminDataCard from '@/Components/Admin/AdminDataCard.vue';
import AdminErrorSummary from '@/Components/Admin/AdminErrorSummary.vue';
import AdminFormField from '@/Components/Admin/AdminFormField.vue';
import AdminMediaPicker from '@/Components/Admin/AdminMediaPicker.vue';
import AdminPageHeader from '@/Components/Admin/AdminPageHeader.vue';
import AdminSelect from '@/Components/Admin/AdminSelect.vue';
import AdminTextInput from '@/Components/Admin/AdminTextInput.vue';
import AdminTextarea from '@/Components/Admin/AdminTextarea.vue';
import AdvancedTextEditor from '@/Components/AdvancedTextEditor.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({ page: { type: Object, required: true } });
const module = props.page.module;
const post = module.post;
const featuredPicker = ref(false);
const galleryPicker = ref(false);
const featuredPreviewUrl = ref(post?.featured_image_url || null);
const galleryMedia = ref(post?.gallery || []);
const form = useForm({
    title: post?.title || '',
    slug: post?.slug || '',
    post_category_id: post?.post_category_id || '',
    summary: post?.summary || '',
    content: post?.content || '',
    status: post?.status || 'draft',
    featured_media_id: post?.featured_media_id || null,
    gallery_media_ids: galleryMedia.value.map((media) => media.media_id),
    version: post?.version || null,
});
const setGallery = (selection) => {
    galleryMedia.value = Array.isArray(selection) ? selection : [selection];
    form.gallery_media_ids = galleryMedia.value.map((media) => media.id || media.media_id);
    galleryPicker.value = false;
};
const removeGalleryImage = (index) => {
    galleryMedia.value.splice(index, 1);
    form.gallery_media_ids = galleryMedia.value.map((media) => media.id || media.media_id);
};
const selectFeatured = (media) => {
    form.featured_media_id = media.id;
    featuredPreviewUrl.value = media.url;
    featuredPicker.value = false;
};
const clearMedia = () => {
    form.featured_media_id = null;
    featuredPreviewUrl.value = null;
};
const syncFromPage = (page) => {
    const updated = page.props.page?.module?.post;
    if (!updated) return;
    if (updated.version) form.version = updated.version;
    galleryMedia.value = updated.gallery || [];
    form.gallery_media_ids = galleryMedia.value.map((media) => media.media_id);
    featuredPreviewUrl.value = updated.featured_image_url || null;
};
const save = () => {
    const options = { onSuccess: syncFromPage };
    if (post) form.put(route('admin.posts.update', post.id), options);
    else form.post(route('admin.posts.store'), options);
};
</script>

<template>
    <Head :title="page.meta.title" />
    <AuthenticatedLayout>
        <AdminPageHeader :title="page.meta.title"><Link :href="route('admin.posts.index')" class="text-sm text-admin-content-muted">Quay lại</Link></AdminPageHeader>
        <div class="mt-6 space-y-6">
            <AdminErrorSummary :errors="form.errors" />
            <AdminDataCard title="Thông tin bài viết">
                <form class="space-y-5" @submit.prevent="save">
                    <div class="grid gap-4 md:grid-cols-2">
                        <AdminFormField label="Tiêu đề" for-id="post-title" :error="form.errors.title"><AdminTextInput id="post-title" v-model="form.title" /></AdminFormField>
                        <AdminFormField label="Đường dẫn" for-id="post-slug" :error="form.errors.slug"><AdminTextInput id="post-slug" v-model="form.slug" /></AdminFormField>
                        <AdminFormField label="Danh mục" for-id="post-category" :error="form.errors.post_category_id"><AdminSelect id="post-category" v-model="form.post_category_id" :options="[{ key: '', label: 'Chưa phân loại' }, ...module.categories.map((category) => ({ key: category.id, label: category.name }))]" /></AdminFormField>
                        <AdminFormField label="Trạng thái" for-id="post-status" :error="form.errors.status"><AdminSelect id="post-status" v-model="form.status" :options="module.statuses" /></AdminFormField>
                    </div>
                    <AdminFormField label="Tóm tắt" for-id="post-summary" :error="form.errors.summary"><AdminTextarea id="post-summary" v-model="form.summary" rows="4" /></AdminFormField>
                    <AdminFormField label="Nội dung" for-id="post-content" :error="form.errors.content"><AdvancedTextEditor id="post-content" v-model="form.content" :height="360" /></AdminFormField>
                    <AdminFormField label="Ảnh nổi bật" :error="form.errors.featured_media_id">
                        <div class="flex items-center gap-3">
                            <img v-if="featuredPreviewUrl" :src="featuredPreviewUrl" :alt="form.title || 'Ảnh nổi bật'" class="h-20 w-32 rounded object-cover" />
                            <button type="button" class="rounded border border-admin-border px-4 py-2 text-sm text-admin-content" @click="featuredPicker = true">Chọn ảnh đại diện</button>
                            <button v-if="form.featured_media_id" type="button" class="border border-admin-border px-3 py-2 text-sm text-admin-danger" @click="clearMedia">Xóa ảnh</button>
                        </div>
                    </AdminFormField>
                    <div class="flex justify-end"><button type="submit" :disabled="form.processing" class="rounded bg-admin-accent px-5 py-2 text-sm font-semibold text-admin-page">{{ form.processing ? 'Đang lưu...' : 'Lưu bài viết' }}</button></div>
                </form>
            </AdminDataCard>
            <AdminDataCard title="Album bài viết">
                <div class="flex flex-wrap items-center gap-3"><button type="button" class="rounded border border-admin-border px-4 py-2 text-sm text-admin-content" @click="galleryPicker = true">Chọn ảnh album từ Media</button><span class="text-sm text-admin-content-muted">{{ galleryMedia.length }} ảnh</span></div>
                <div v-if="galleryMedia.length" class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-6"><div v-for="(media, index) in galleryMedia" :key="media.media_id || media.id" class="relative border border-admin-border p-2"><img :src="media.url" :alt="media.alt_text || media.file_name || form.title" class="aspect-square w-full rounded object-cover" /><button type="button" class="absolute right-3 top-3 rounded bg-admin-danger px-2 py-1 text-xs text-white" :aria-label="`Xóa ảnh album ${index + 1}`" @click="removeGalleryImage(index)">×</button></div></div>
                <p v-else class="mt-4 text-sm text-admin-content-muted">Chưa có ảnh album.</p>
            </AdminDataCard>
        </div>
        <AdminMediaPicker :open="featuredPicker" :selected-id="form.featured_media_id" media-type="image" title="Chọn ảnh đại diện" @close="featuredPicker = false" @select="selectFeatured" />
        <AdminMediaPicker :open="galleryPicker" :selected-ids="form.gallery_media_ids" :multiple="true" media-type="image" title="Chọn ảnh album bài viết" @close="galleryPicker = false" @select="setGallery" />
    </AuthenticatedLayout>
</template>
