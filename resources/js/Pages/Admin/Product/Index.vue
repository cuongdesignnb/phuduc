<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AdminConfirmDialog from '@/Components/Admin/AdminConfirmDialog.vue';
import AdminDataCard from '@/Components/Admin/AdminDataCard.vue';
import AdminPageHeader from '@/Components/Admin/AdminPageHeader.vue';
import AdminPagination from '@/Components/Admin/AdminPagination.vue';
import AdminStatusBadge from '@/Components/Admin/AdminStatusBadge.vue';
import AdminTable from '@/Components/Admin/AdminTable.vue';
import AdminTextInput from '@/Components/Admin/AdminTextInput.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({ page: { type: Object, required: true } });
const module = props.page.module;
const search = ref(module.filters.search || '');
const status = ref(module.filters.status || '');
const deleting = ref(null);
let timer;
watch([search, status], () => { clearTimeout(timer); timer = setTimeout(() => router.get(route('admin.products.index'), { search: search.value || undefined, status: status.value || undefined }, { preserveState: true, replace: true }), 300); });
const requestDelete = (product) => { deleting.value = product; };
const deleteProduct = () => { router.delete(deleting.value.delete_url, { preserveScroll: true, onFinish: () => { deleting.value = null; } }); };
</script>

<template>
    <Head :title="page.meta.title" />
    <AuthenticatedLayout>
        <AdminPageHeader :title="page.meta.title" description="Quản lý sản phẩm và tồn kho"><Link :href="route('admin.products.create')" class="rounded-lg bg-admin-accent px-4 py-2 text-sm font-semibold text-admin-page focus:outline-none focus:ring-2 focus:ring-admin-focus">Thêm sản phẩm</Link></AdminPageHeader>
        <AdminDataCard title="Bộ lọc sản phẩm" class="mt-6"><div class="grid gap-3 sm:grid-cols-2"><AdminTextInput v-model="search" aria-label="Tìm sản phẩm" placeholder="Tìm theo tên hoặc SKU" /><select v-model="status" class="border border-admin-border bg-admin-page px-3 py-2 text-sm text-admin-content focus:outline-none focus:ring-2 focus:ring-admin-focus" aria-label="Lọc trạng thái"><option value="">Tất cả trạng thái</option><option value="active">Đang bán</option><option value="inactive">Ngừng bán</option></select></div></AdminDataCard>
        <AdminDataCard title="Danh sách sản phẩm" class="mt-6"><AdminTable :columns="[{ key: 'image', label: 'Ảnh' }, { key: 'name', label: 'Sản phẩm' }, { key: 'price', label: 'Giá' }, { key: 'stock', label: 'Kho' }, { key: 'status', label: 'Trạng thái' }, { key: 'actions', label: 'Thao tác' }]"><tr v-for="product in module.items" :key="product.id" class="border-b border-admin-border"><td class="px-3 py-3"><img v-if="product.image_url" :src="product.image_url" :alt="product.name" class="h-12 w-12 object-cover" /><span v-else class="text-xs text-admin-content-muted">Chưa có ảnh</span></td><td class="px-3 py-3 text-sm font-medium text-admin-content"><Link :href="product.edit_url" class="hover:text-admin-accent">{{ product.name }}</Link><p class="text-xs text-admin-content-muted">{{ product.sku || 'Không có SKU' }}</p></td><td class="px-3 py-3 text-sm text-admin-content">{{ product.price_display }}</td><td class="px-3 py-3 text-sm text-admin-content">{{ product.stock_label }}</td><td class="px-3 py-3"><AdminStatusBadge :status="product.status" /></td><td class="px-3 py-3 text-right"><Link :href="product.edit_url" class="mr-3 text-sm text-admin-accent">Sửa</Link><button v-if="product.can_delete" type="button" class="text-sm text-admin-danger" @click="requestDelete(product)">Xóa</button><span v-else class="text-xs text-admin-content-muted">Có tham chiếu</span></td></tr><tr v-if="!module.items.length"><td colspan="6" class="px-3 py-10 text-center text-sm text-admin-content-muted">Chưa có sản phẩm phù hợp.</td></tr></AdminTable><AdminPagination class="mt-4" :pagination="module.pagination" /></AdminDataCard>
        <AdminConfirmDialog :open="!!deleting" title="Xóa sản phẩm" message="Sản phẩm không có tham chiếu sẽ bị xóa cùng tệp ảnh do sản phẩm sở hữu." confirm-label="Xóa sản phẩm" danger @cancel="deleting = null" @confirm="deleteProduct" />
    </AuthenticatedLayout>
</template>
