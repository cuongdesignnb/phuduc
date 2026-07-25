<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AdminConfirmDialog from '@/Components/Admin/AdminConfirmDialog.vue';
import AdminDataCard from '@/Components/Admin/AdminDataCard.vue';
import AdminFormField from '@/Components/Admin/AdminFormField.vue';
import AdminPageHeader from '@/Components/Admin/AdminPageHeader.vue';
import AdminStatusBadge from '@/Components/Admin/AdminStatusBadge.vue';
import AdminTable from '@/Components/Admin/AdminTable.vue';
import AdminTextarea from '@/Components/Admin/AdminTextarea.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({ page: { type: Object, required: true } });
const order = props.page.module.order;
const form = useForm({ status: '', reason: '', version: order.version });
const confirming = ref(false);
const options = order.allowed_next_statuses || [];
const submit = () => {
    if (form.status === 'cancelled') { confirming.value = true; return; }
    form.patch(route('admin.orders.updateStatus', order.id), { preserveScroll: true });
};
const confirmCancellation = () => { confirming.value = false; form.patch(route('admin.orders.updateStatus', order.id), { preserveScroll: true }); };
</script>

<template>
    <Head :title="page.meta.title + ' ' + order.order_number" />
    <AuthenticatedLayout>
        <AdminPageHeader :title="'Đơn hàng ' + order.order_number" description="Chi tiết snapshot lịch sử và dòng trạng thái" />
        <div class="mt-6 grid gap-6 lg:grid-cols-2">
            <AdminDataCard title="Thông tin khách hàng"><dl class="grid gap-3 text-sm sm:grid-cols-2"><div><dt class="text-admin-content-muted">Tên</dt><dd class="text-admin-content">{{ order.customer.name || 'Chưa cập nhật' }}</dd></div><div><dt class="text-admin-content-muted">Điện thoại</dt><dd class="text-admin-content">{{ order.customer.phone || 'Chưa cập nhật' }}</dd></div><div><dt class="text-admin-content-muted">Email</dt><dd class="text-admin-content">{{ order.customer.email || 'Chưa cập nhật' }}</dd></div><div class="sm:col-span-2"><dt class="text-admin-content-muted">Địa chỉ giao hàng</dt><dd class="whitespace-pre-line text-admin-content">{{ order.shipping_address || 'Chưa cập nhật' }}</dd></div><div class="sm:col-span-2"><dt class="text-admin-content-muted">Ghi chú</dt><dd class="whitespace-pre-line text-admin-content">{{ order.notes || 'Không có ghi chú' }}</dd></div></dl></AdminDataCard>
            <AdminDataCard title="Cập nhật trạng thái"><div class="space-y-4"><div class="flex items-center gap-3"><span class="text-sm text-admin-content-muted">Hiện tại</span><AdminStatusBadge :status="order.status" /></div><AdminFormField label="Trạng thái tiếp theo" for-id="order-status" :error="form.errors.status"><select id="order-status" v-model="form.status" class="w-full border border-admin-border bg-admin-page px-3 py-2 text-sm text-admin-content"><option value="">Chọn trạng thái</option><option v-for="item in options" :key="item.key" :value="item.key">{{ item.label }}</option></select></AdminFormField><AdminFormField v-if="form.status === 'cancelled'" label="Lý do hủy" for-id="order-reason" :error="form.errors.reason"><AdminTextarea id="order-reason" v-model="form.reason" rows="4" placeholder="Nhập lý do bắt buộc" /></AdminFormField><p v-if="form.errors.version" class="text-sm text-admin-danger" role="alert">{{ form.errors.version }}</p><button type="button" :disabled="!form.status || form.processing" class="rounded-lg bg-admin-accent px-4 py-2 text-sm font-semibold text-admin-page disabled:opacity-50" @click="submit">Cập nhật trạng thái</button></div></AdminDataCard>
        </div>
        <AdminDataCard title="Sản phẩm trong đơn" class="mt-6"><AdminTable :columns="[{ key: 'product', label: 'Snapshot sản phẩm' }, { key: 'quantity', label: 'Số lượng' }, { key: 'price', label: 'Đơn giá' }, { key: 'subtotal', label: 'Thành tiền' }]" ><tr v-for="item in order.items" :key="item.id" class="border-b border-admin-border"><td class="px-3 py-3 text-sm text-admin-content">{{ item.product_name }}<Link v-if="item.current_product_url" :href="item.current_product_url" class="ml-2 text-xs text-admin-accent">Sản phẩm hiện tại</Link></td><td class="px-3 py-3 text-sm text-admin-content-muted">{{ item.quantity }}</td><td class="px-3 py-3 text-sm text-admin-content">{{ item.unit_price_display }}</td><td class="px-3 py-3 text-sm text-admin-content">{{ item.subtotal_display }}</td></tr></AdminTable><div class="mt-4 flex flex-wrap justify-between gap-3 text-sm"><span class="text-admin-content-muted">Tổng lưu trong đơn: <strong class="text-admin-content">{{ order.total_display }}</strong></span><span :class="order.total_parity.is_equal ? 'text-admin-success' : 'text-admin-danger'">{{ order.total_parity.is_equal ? 'Tổng khớp snapshot' : order.total_parity.warning }}</span></div></AdminDataCard>
        <AdminDataCard title="Lịch sử trạng thái" class="mt-6"><ol v-if="order.status_history.length" class="space-y-3"><li v-for="(item, index) in order.status_history" :key="index" class="border-l-2 border-admin-accent pl-4 text-sm"><p class="text-admin-content"><span>{{ item.from_status.label }}</span> → <span>{{ item.to_status.label }}</span></p><p class="text-admin-content-muted">{{ item.created_at_display }}</p><p v-if="item.reason" class="mt-1 text-admin-content">{{ item.reason }}</p></li></ol><p v-else class="text-sm text-admin-content-muted">Chưa có lần chuyển trạng thái.</p></AdminDataCard>
        <AdminConfirmDialog :open="confirming" title="Xác nhận hủy đơn" message="Thao tác sẽ hoàn lại tồn kho cho các sản phẩm còn liên kết. Lý do hủy là bắt buộc." confirm-label="Hủy đơn hàng" danger :processing="form.processing" @cancel="confirming = false" @confirm="confirmCancellation" />
    </AuthenticatedLayout>
</template>
