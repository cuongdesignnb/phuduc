<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({ order: Object });
const statusLabels = { pending: 'Chờ xử lý', processing: 'Đang xử lý', shipping: 'Đang giao', completed: 'Hoàn thành', cancelled: 'Đã hủy' };
const newStatus = ref(props.order.status);

const updateStatus = () => {
    router.patch(route('admin.orders.updateStatus', props.order.id), { status: newStatus.value }, { preserveScroll: true });
};

const formatPrice = (p) => new Intl.NumberFormat('vi-VN').format(p) + '₫';
</script>

<template>
    <Head :title="'Đơn hàng #' + order.order_number" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-display font-bold text-white">Đơn hàng #{{ order.order_number }}</h2>
        </template>
        <div class="py-6">
            <div class="mx-auto max-w-7xl px-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="rounded-2xl border border-white/5 bg-carbon-900/50 backdrop-blur-sm p-6">
                        <h3 class="text-lg font-display font-semibold text-white mb-4">Thông tin khách hàng</h3>
                        <dl class="space-y-3 text-sm">
                            <div class="flex justify-between"><dt class="text-carbon-500">Tên:</dt><dd class="text-white">{{ $fixText(order.customer_name) }}</dd></div>
                            <div class="flex justify-between"><dt class="text-carbon-500">SĐT:</dt><dd class="text-white">{{ order.customer_phone }}</dd></div>
                            <div class="flex justify-between"><dt class="text-carbon-500">Email:</dt><dd class="text-white">{{ order.customer_email || '—' }}</dd></div>
                            <div><dt class="text-carbon-500 mb-1">Địa chỉ giao:</dt><dd class="text-carbon-300">{{ $fixText(order.shipping_address) }}</dd></div>
                            <div v-if="order.notes"><dt class="text-carbon-500 mb-1">Ghi chú:</dt><dd class="text-carbon-300">{{ $fixText(order.notes) }}</dd></div>
                        </dl>
                    </div>
                    <div class="rounded-2xl border border-white/5 bg-carbon-900/50 backdrop-blur-sm p-6">
                        <h3 class="text-lg font-display font-semibold text-white mb-4">Cập nhật trạng thái</h3>
                        <div class="flex gap-3 items-end">
                            <div class="flex-1">
                                <select v-model="newStatus" class="w-full rounded-xl border border-white/10 bg-carbon-800 text-white text-sm py-2.5 px-4 focus:border-volt-500/50 focus:ring-1 focus:ring-volt-500/20">
                                    <option v-for="(label, key) in statusLabels" :key="key" :value="key">{{ label }}</option>
                                </select>
                            </div>
                            <button @click="updateStatus" class="rounded-xl bg-volt-500 px-5 py-2.5 text-sm font-semibold text-white hover:bg-volt-600 transition-all shadow-lg shadow-volt-500/20">Cập nhật</button>
                        </div>
                        <div class="mt-5 space-y-2">
                            <p class="text-sm text-carbon-400">Tổng tiền: <span class="text-xl font-display font-bold text-volt-400">{{ formatPrice(order.total_amount) }}</span></p>
                            <p class="text-sm text-carbon-500">Ngày tạo: {{ new Date(order.created_at).toLocaleString('vi-VN') }}</p>
                        </div>
                    </div>
                </div>
                <div class="rounded-2xl border border-white/5 bg-carbon-900/50 backdrop-blur-sm p-6">
                    <h3 class="text-lg font-display font-semibold text-white mb-4">Sản phẩm trong đơn</h3>
                    <table class="min-w-full divide-y divide-white/5">
                        <thead><tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-carbon-500 uppercase">Sản phẩm</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-carbon-500 uppercase">Đơn giá</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-carbon-500 uppercase">SL</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-carbon-500 uppercase">Thành tiền</th>
                        </tr></thead>
                        <tbody class="divide-y divide-white/[.03]">
                            <tr v-for="item in order.items" :key="item.id" class="hover:bg-white/[.02] transition-colors">
                                <td class="px-4 py-3 text-sm text-white">{{ $fixText(item.product_name) }}</td>
                                <td class="px-4 py-3 text-sm text-carbon-400">{{ formatPrice(item.price) }}</td>
                                <td class="px-4 py-3 text-sm text-carbon-400">{{ item.quantity }}</td>
                                <td class="px-4 py-3 text-sm text-volt-400 font-semibold">{{ formatPrice(item.total) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
