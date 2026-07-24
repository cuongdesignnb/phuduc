<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AdminConfirmDialog from '@/Components/Admin/AdminConfirmDialog.vue';
import AdminDataCard from '@/Components/Admin/AdminDataCard.vue';
import AdminErrorSummary from '@/Components/Admin/AdminErrorSummary.vue';
import AdminFormField from '@/Components/Admin/AdminFormField.vue';
import AdminPageHeader from '@/Components/Admin/AdminPageHeader.vue';
import AdminSelect from '@/Components/Admin/AdminSelect.vue';
import AdminTextInput from '@/Components/Admin/AdminTextInput.vue';
import MenuItemTree from '@/Components/MenuItemTree.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({ page: { type: Object, required: true } });
const module = props.page.module;
const menu = module.menu;
const form = useForm({ name: menu?.name || '', location: menu?.location || 'header', version: menu?.version || null });
const items = ref(menu?.items || []);
const itemForm = useForm({ items: items.value, version: menu?.version || null });
const pendingRemove = ref(null);
const descendants = (item) => (item.children || []).reduce((total, child) => total + 1 + descendants(child), 0);
const findAndRemove = (list, target) => { const index = list.indexOf(target); if (index >= 0) { list.splice(index, 1); return true; } return list.some((item) => findAndRemove(item.children || [], target)); };
const syncVersion = (page) => { const version = page.props.page?.module?.menu?.version || page.props.flash?.admin_version; if (version) { form.version = version; itemForm.version = version; } };
const save = () => menu ? form.put(route('admin.menus.update', menu.id), { onSuccess: (page) => { syncVersion(page); form.defaults(form.data()); form.reset(); itemForm.defaults({ items: items.value, version: itemForm.version }); } }) : form.post(route('admin.menus.store'));
const requestRemove = (item) => { pendingRemove.value = item; };
const confirmRemove = () => { if (pendingRemove.value) findAndRemove(items.value, pendingRemove.value); pendingRemove.value = null; };
const saveItems = () => { itemForm.items = items.value; itemForm.post(route('admin.menus.items', menu.id), { preserveScroll: true, onSuccess: (page) => { syncVersion(page); itemForm.defaults({ items: items.value, version: itemForm.version }); itemForm.reset(); form.defaults(form.data()); } }); };
</script>

<template>
    <Head :title="page.meta.title" />
    <AuthenticatedLayout>
        <AdminPageHeader :title="page.meta.title"><Link :href="route('admin.menus.index')" class="text-sm text-admin-content-muted">Quay lại</Link></AdminPageHeader>
        <div class="mt-6 space-y-6">
            <AdminErrorSummary :errors="{ ...form.errors, ...itemForm.errors }" />
            <AdminDataCard title="Thông tin menu"><form class="grid gap-4 sm:grid-cols-2" @submit.prevent="save"><AdminFormField label="Tên menu" for-id="menu-name" :error="form.errors.name"><AdminTextInput id="menu-name" v-model="form.name" /></AdminFormField><AdminFormField label="Vị trí" for-id="menu-location" :error="form.errors.location"><AdminSelect id="menu-location" v-model="form.location" :options="Object.entries(module.locations).map(([key, value]) => ({ key, label: value.label }))" /></AdminFormField><div class="sm:col-span-2"><button type="submit" :disabled="form.processing" class="rounded-lg bg-admin-accent px-5 py-2 text-sm font-semibold text-admin-page">Lưu thông tin</button></div></form></AdminDataCard>
            <AdminDataCard v-if="menu" title="Cấu trúc menu"><MenuItemTree v-model="items" :targets="module.targets" @remove="requestRemove" /><div class="mt-5 flex justify-end"><button type="button" :disabled="itemForm.processing" class="rounded-lg bg-admin-accent px-5 py-2 text-sm font-semibold text-admin-page" @click="saveItems">Lưu cấu trúc</button></div></AdminDataCard>
        </div>
        <AdminConfirmDialog :open="!!pendingRemove" title="Xóa mục menu" :message="pendingRemove ? `Thao tác này cũng xóa ${descendants(pendingRemove)} mục con.` : ''" confirm-label="Xóa mục" danger @cancel="pendingRemove = null" @confirm="confirmRemove" />
    </AuthenticatedLayout>
</template>
