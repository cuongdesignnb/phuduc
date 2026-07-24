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
const module = props.page.module; const post = module.post; const picker = ref(false);
const form = useForm({ title: post?.title || '', slug: post?.slug || '', post_category_id: post?.post_category_id || '', summary: post?.summary || '', content: post?.content || '', status: post?.status || 'draft', featured_media_id: post?.featured_media_id || null, version: post?.version || null });
const save = () => post ? form.put(route('admin.posts.update', post.id), { onSuccess: (page) => { if (page.props.page?.module?.post?.version) form.version = page.props.page.module.post.version; form.defaults(form.data()); form.reset(); } }) : form.post(route('admin.posts.store'));
const selectMedia = (media) => { form.featured_media_id = media.id; picker.value = false; };
const clearMedia = () => { form.featured_media_id = null; };
</script>

<template>
    <Head :title="page.meta.title" /><AuthenticatedLayout><AdminPageHeader :title="page.meta.title"><Link :href="route('admin.posts.index')" class="text-sm text-admin-content-muted">Quay lại</Link></AdminPageHeader><div class="mt-6 space-y-6"><AdminErrorSummary :errors="form.errors" /><AdminDataCard title="Thông tin bài viết"><form class="space-y-5" @submit.prevent="save"><div class="grid gap-4 md:grid-cols-2"><AdminFormField label="Tiêu đề" for-id="post-title" :error="form.errors.title"><AdminTextInput id="post-title" v-model="form.title" /></AdminFormField><AdminFormField label="Đường dẫn" for-id="post-slug" :error="form.errors.slug"><AdminTextInput id="post-slug" v-model="form.slug" /></AdminFormField><AdminFormField label="Danh mục" for-id="post-category" :error="form.errors.post_category_id"><AdminSelect id="post-category" v-model="form.post_category_id" :options="[{ key: '', label: 'Chưa phân loại' }, ...module.categories.map(category => ({ key: category.id, label: category.name }))]" /></AdminFormField><AdminFormField label="Trạng thái" for-id="post-status" :error="form.errors.status"><AdminSelect id="post-status" v-model="form.status" :options="module.statuses" /></AdminFormField></div><AdminFormField label="Tóm tắt" for-id="post-summary" :error="form.errors.summary"><AdminTextarea id="post-summary" v-model="form.summary" rows="4" /></AdminFormField><AdminFormField label="Nội dung" for-id="post-content" :error="form.errors.content"><AdvancedTextEditor id="post-content" v-model="form.content" :height="360" /></AdminFormField><AdminFormField label="Ảnh nổi bật" :error="form.errors.featured_media_id"><div class="flex items-center gap-3"><img v-if="post?.featured_image_url" :src="post.featured_image_url" alt="" class="h-16 w-24 object-cover" /><button type="button" class="rounded border border-admin-border px-4 py-2 text-sm text-admin-content" @click="picker = true">Chọn tệp</button><button v-if="form.featured_media_id" type="button" class="border border-admin-border px-3 py-2 text-sm text-admin-danger" @click="clearMedia">Xóa ảnh</button><span v-if="form.featured_media_id" class="text-sm text-admin-content-muted">Tệp #{{ form.featured_media_id }}</span></div></AdminFormField><div class="flex justify-end"><button type="submit" :disabled="form.processing" class="rounded bg-admin-accent px-5 py-2 text-sm font-semibold text-admin-page">{{ form.processing ? 'Đang lưu...' : 'Lưu bài viết' }}</button></div></form></AdminDataCard></div><AdminMediaPicker :open="picker" :selected-id="form.featured_media_id" media-type="image" @close="picker = false" @select="selectMedia" /></AuthenticatedLayout>
</template>
