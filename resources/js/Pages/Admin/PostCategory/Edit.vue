<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AdminDataCard from '@/Components/Admin/AdminDataCard.vue';
import AdminErrorSummary from '@/Components/Admin/AdminErrorSummary.vue';
import AdminFormField from '@/Components/Admin/AdminFormField.vue';
import AdminPageHeader from '@/Components/Admin/AdminPageHeader.vue';
import AdminSelect from '@/Components/Admin/AdminSelect.vue';
import AdminTextInput from '@/Components/Admin/AdminTextInput.vue';
import AdminTextarea from '@/Components/Admin/AdminTextarea.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
const props = defineProps({ page: { type: Object, required: true } }); const module = props.page.module; const category = module.category; const form = useForm({ name: category?.name || '', slug: category?.slug || '', parent_id: category?.parent_id || '', description: category?.description || '' }); const save = () => category ? form.put(route('admin.post-categories.update', category.id)) : form.post(route('admin.post-categories.store'));
</script>

<template><Head :title="page.meta.title" /><AuthenticatedLayout><AdminPageHeader :title="page.meta.title"><Link :href="route('admin.post-categories.index')" class="text-sm text-admin-content-muted">Quay lại</Link></AdminPageHeader><AdminDataCard title="Thông tin danh mục" class="mt-6"><AdminErrorSummary :errors="form.errors" /><form class="mt-5 space-y-5" @submit.prevent="save"><AdminFormField label="Tên danh mục" for-id="category-name" :error="form.errors.name"><AdminTextInput id="category-name" v-model="form.name" /></AdminFormField><AdminFormField label="Slug" for-id="category-slug" :error="form.errors.slug"><AdminTextInput id="category-slug" v-model="form.slug" /></AdminFormField><AdminFormField label="Danh mục cha" for-id="category-parent" :error="form.errors.parent_id"><AdminSelect id="category-parent" v-model="form.parent_id" :options="[{ key: '', label: 'Không có danh mục cha' }, ...module.parents.map(parent => ({ key: parent.id, label: parent.name }))]" /></AdminFormField><AdminFormField label="Mô tả" for-id="category-description" :error="form.errors.description"><AdminTextarea id="category-description" v-model="form.description" /></AdminFormField><div class="flex justify-end"><button type="submit" :disabled="form.processing" class="rounded-lg bg-admin-accent px-5 py-2 text-sm font-semibold text-admin-page">Lưu danh mục</button></div></form></AdminDataCard></AuthenticatedLayout></template>
