<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AdminConfirmDialog from '@/Components/Admin/AdminConfirmDialog.vue';
import AdminDataCard from '@/Components/Admin/AdminDataCard.vue';
import AdminPageHeader from '@/Components/Admin/AdminPageHeader.vue';
import CategoryTree from '@/Components/Admin/CategoryTree.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
const props = defineProps({ page: { type: Object, required: true } }); const deleting = ref(null); const destroy = () => router.delete(deleting.value.delete_url, { onFinish: () => { deleting.value = null; } });
</script>

<template><Head :title="page.meta.title" /><AuthenticatedLayout><AdminPageHeader :title="page.meta.title"><Link :href="route('admin.post-categories.create')" class="rounded bg-admin-accent px-4 py-2 text-sm font-semibold text-admin-page">Thêm danh mục</Link></AdminPageHeader><AdminDataCard title="Cây danh mục" class="mt-6"><CategoryTree :items="page.module.items" @remove="deleting = $event" /><p v-if="!page.module.items.length" class="py-10 text-center text-sm text-admin-content-muted">Chưa có danh mục.</p></AdminDataCard><AdminConfirmDialog :open="!!deleting" title="Xóa danh mục" message="Danh mục có bài viết hoặc danh mục con không thể xóa." confirm-label="Xóa danh mục" danger @cancel="deleting = null" @confirm="destroy" /></AuthenticatedLayout></template>
