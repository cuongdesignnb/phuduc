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
const aiBusy = ref(false);
const aiError = ref('');
const featuredPreviewUrl = ref(post?.featured_image_url || null);
const galleryMedia = ref(post?.gallery || []);
const form = useForm({
    title: post?.title || '',
    slug: post?.slug || '',
    post_category_id: post?.post_category_id || '',
    summary: post?.summary || '',
    content: post?.content || '',
    meta_title: post?.meta_title || '',
    meta_description: post?.meta_description || '',
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
const generateArticle = async (withImages = false) => {
    if (!form.title.trim()) {
        aiError.value = 'Hãy nhập tiêu đề hoặc chủ đề trước khi sinh bài.';
        return;
    }
    aiBusy.value = true;
    aiError.value = '';
    try {
        const response = await window.axios.post(route('admin.ai.content.generate'), {
            type: 'article',
            topic: form.title,
            keywords: [],
            tone: 'professional',
            length: 'medium',
            full_article: true,
            existing_content: form.content,
            category_id: form.post_category_id || null,
            with_images: withImages,
            image_count: withImages ? 1 : 1,
        });
        const generated = response.data.result || {};
        form.title = generated.title || form.title;
        form.summary = generated.excerpt || form.summary;
        form.content = generated.content || form.content;
        form.meta_title = generated.meta_title || form.meta_title;
        form.meta_description = generated.meta_desc || form.meta_description;
        if (withImages && generated.images?.length) {
            const images = generated.images.map((image) => ({ media_id: image.media_id, id: image.media_id, url: image.url, alt_text: image.alt, file_name: image.alt }));
            galleryMedia.value = images;
            form.gallery_media_ids = images.map((image) => image.media_id);
            form.featured_media_id = images[0].media_id;
            featuredPreviewUrl.value = images[0].url;
        }
    } catch (exception) {
        aiError.value = exception.response?.data?.message || 'Không thể sinh bài viết. Kiểm tra cấu hình AI và thử lại.';
    } finally {
        aiBusy.value = false;
    }
};
</script>

<template>
    <Head :title="page.meta.title" />
    <AuthenticatedLayout>
        <AdminPageHeader :title="page.meta.title"><Link :href="route('admin.posts.index')" class="text-sm text-admin-content-muted">Quay lại</Link></AdminPageHeader>
        <div class="mt-6 space-y-6">
            <AdminErrorSummary :errors="form.errors" />
            <AdminDataCard title="Thông tin bài viết">
                <div class="mb-5 flex flex-wrap items-center gap-3 rounded border border-admin-border bg-admin-page p-3"><p class="mr-auto text-sm text-admin-content-muted">AI có thể điền nội dung, meta SEO và ảnh thumbnail vào biểu mẫu.</p><button type="button" class="rounded border border-admin-border px-4 py-2 text-sm text-admin-content" :disabled="aiBusy" @click="generateArticle(false)">{{ aiBusy ? 'Đang sinh...' : 'Sinh bài viết + Meta' }}</button><button type="button" class="rounded bg-admin-accent px-4 py-2 text-sm font-semibold text-admin-page" :disabled="aiBusy" @click="generateArticle(true)">{{ aiBusy ? 'Đang sinh...' : 'Sinh bài viết kèm ảnh + thumbnail' }}</button></div>
                <p v-if="aiError" class="mb-4 text-sm text-admin-danger" role="alert">{{ aiError }}</p>
                <form class="space-y-5" @submit.prevent="save">
                    <div class="grid gap-4 md:grid-cols-2">
                        <AdminFormField label="Tiêu đề" for-id="post-title" :error="form.errors.title"><AdminTextInput id="post-title" v-model="form.title" /></AdminFormField>
                        <AdminFormField label="Đường dẫn" for-id="post-slug" :error="form.errors.slug"><AdminTextInput id="post-slug" v-model="form.slug" /></AdminFormField>
                        <AdminFormField label="Danh mục" for-id="post-category" :error="form.errors.post_category_id"><AdminSelect id="post-category" v-model="form.post_category_id" :options="[{ key: '', label: 'Chưa phân loại' }, ...module.categories.map((category) => ({ key: category.id, label: category.name }))]" /></AdminFormField>
                        <AdminFormField label="Trạng thái" for-id="post-status" :error="form.errors.status"><AdminSelect id="post-status" v-model="form.status" :options="module.statuses" /></AdminFormField>
                    </div>
                    <AdminFormField label="Tóm tắt" for-id="post-summary" :error="form.errors.summary"><AdminTextarea id="post-summary" v-model="form.summary" rows="4" /></AdminFormField>
                    <AdminFormField label="Nội dung" for-id="post-content" :error="form.errors.content"><AdvancedTextEditor id="post-content" v-model="form.content" :height="360" /></AdminFormField>
                    <div class="grid gap-4 md:grid-cols-2"><AdminFormField label="Meta title" for-id="post-meta-title" :error="form.errors.meta_title"><AdminTextInput id="post-meta-title" v-model="form.meta_title" /></AdminFormField><AdminFormField label="Meta description" for-id="post-meta-description" :error="form.errors.meta_description"><AdminTextarea id="post-meta-description" v-model="form.meta_description" rows="3" /></AdminFormField></div>
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
