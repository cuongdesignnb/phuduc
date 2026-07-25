<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
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
const dateFrom = ref(module.filters.date_from || '');
const dateTo = ref(module.filters.date_to || '');
let timer;
const reload = () => router.get(route('admin.orders.index'), { search: search.value || undefined, status: status.value || undefined, date_from: dateFrom.value || undefined, date_to: dateTo.value || undefined }, { preserveState: true, replace: true });
watch([search, status, dateFrom, dateTo], () => { clearTimeout(timer); timer = setTimeout(reload, 250); });
</script>

<template>
    <Head :title="page.meta.title" />
    <AuthenticatedLayout>
        <AdminPageHeader :title="page.meta.title" description="Theo dõi đơn hàng, trạng thái và tồn kho hoàn trả" />
        <AdminDataCard title="Bộ lọc đơn hàng" class="mt-6">
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <AdminTextInput v-model="search" aria-label="Tìm đơn hàng" placeholder="Mã đơn, tên, số điện thoại" />
                <select v-model="status" aria-label="Lọc trạng thái" class="border border-admin-border bg-admin-page px-3 py-2 text-sm text-admin-content"><option value="">Tất cả trạng thái</option><option v-for="item in module.statuses" :key="item.key" :value="item.key">{{ item.label }}</option></select>
                <AdminTextInput v-model="dateFrom" type="date" aria-label="Từ ngày" />
                <AdminTextInput v-model="dateTo" type="date" aria-label="Đến ngày" />
            </div>
        </AdminDataCard>
        <AdminDataCard title="Danh sách đơn hàng" class="mt-6">
            <AdminTable :columns="[{ key: 'order', label: 'Đơn hàng' }, { key: 'customer', label: 'Khách hàng' }, { key: 'items', label: 'Sản phẩm' }, { key: 'total', label: 'Tổng tiền' }, { key: 'status', label: 'Trạng thái' }, { key: 'date', label: 'Ngày tạo' }, { key: 'action', label: 'Thao tác' }]">
                <tr v-for="order in module.items" :key="order.id" class="border-b border-admin-border"><td class="px-3 py-3"><Link :href="order.detail_url" class="font-medium text-admin-accent">{{ order.order_number }}</Link><p class="text-xs text-admin-content-muted">{{ order.items_count }} dòng hàng</p></td><td class="px-3 py-3 text-sm text-admin-content">{{ order.customer_name || 'Chưa cập nhật' }}<p class="text-xs text-admin-content-muted">{{ order.customer_phone || 'Không có số điện thoại' }}</p></td><td class="px-3 py-3 text-sm text-admin-content-muted">{{ order.items_count }}</td><td class="px-3 py-3 text-sm text-admin-content">{{ order.total_display }}</td><td class="px-3 py-3"><AdminStatusBadge :status="order.status" /></td><td class="px-3 py-3 text-sm text-admin-content-muted">{{ order.created_at_display }}</td><td class="px-3 py-3 text-right"><Link :href="order.detail_url" class="text-sm text-admin-accent">Chi tiết</Link></td></tr>
                <tr v-if="!module.items.length"><td colspan="7" class="px-3 py-10 text-center text-sm text-admin-content-muted">Chưa có đơn hàng phù hợp.</td></tr>
            </AdminTable>
            <AdminPagination class="mt-4" :pagination="module.pagination" />
        </AdminDataCard>
    </AuthenticatedLayout>
</template>
