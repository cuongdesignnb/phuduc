<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AdminConfirmDialog from '@/Components/Admin/AdminConfirmDialog.vue';
import AdminDataCard from '@/Components/Admin/AdminDataCard.vue';
import AdminPageHeader from '@/Components/Admin/AdminPageHeader.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
const props = defineProps({ page: { type: Object, required: true } }); const deleting = ref(null); const destroy = () => router.delete(deleting.value.delete_url, { onFinish: () => { deleting.value = null; } });
</script>

<template><Head :title="page.meta.title" /><AuthenticatedLayout><AdminPageHeader :title="page.meta.title"><Link :href="route('admin.menus.create')" class="rounded-lg bg-admin-accent px-4 py-2 text-sm font-semibold text-admin-page">Thêm menu</Link></AdminPageHeader><AdminDataCard title="Menu đã tạo" class="mt-6"><div class="grid gap-3 sm:grid-cols-2"><article v-for="menu in page.module.items" :key="menu.id" class="border border-admin-border p-4"><div class="flex justify-between gap-3"><div><Link :href="menu.edit_url" class="font-medium text-admin-content hover:text-admin-accent">{{ menu.name }}</Link><p class="mt-1 text-sm text-admin-content-muted">{{ menu.location_label }} · {{ menu.items_count }} mục</p></div><button type="button" class="text-sm text-admin-danger" @click="deleting = menu">Xóa</button></div></article></div><p v-if="!page.module.items.length" class="py-10 text-center text-sm text-admin-content-muted">Chưa có menu.</p></AdminDataCard><AdminConfirmDialog :open="!!deleting" title="Xóa menu" message="Toàn bộ mục thuộc menu sẽ được xóa sau khi xác nhận." confirm-label="Xóa menu" danger @cancel="deleting = null" @confirm="destroy" /></AuthenticatedLayout></template>
