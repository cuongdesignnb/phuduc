<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({ orders: Object, filters: Object });
const search = ref(props.filters?.search || '');
const status = ref(props.filters?.status || '');

let t;
watch([search, status], () => {
    clearTimeout(t);
    t = setTimeout(() => {
        router.get(route('admin.orders.index'), { search: search.value || undefined, status: status.value || undefined }, { preserveState: true, replace: true });
    }, 300);
});

const statusLabels = { pending: 'Chờ xử lý', processing: 'Đang xử lý', shipping: 'Đang giao', completed: 'Hoàn thành', cancelled: 'Đã hủy' };
const statusColors = { pending: 'bg-amber-500/10 text-amber-400 border-amber-500/20', processing: 'bg-blue-500/10 text-blue-400 border-blue-500/20', shipping: 'bg-purple-500/10 text-purple-400 border-purple-500/20', completed: 'bg-admin-accent/10 text-admin-accent border-admin-accent/20', cancelled: 'bg-red-500/10 text-red-400 border-red-500/20' };

const formatPrice = (p) => new Intl.NumberFormat('vi-VN').format(p) + '₫';
const paginationLabel = (label) => label.replace(/&laquo;\s*Previous|Previous/g, 'Trước').replace(/Next\s*&raquo;|Next/g, 'Sau').replace(/&laquo;|&raquo;/g, '');
</script>

<template>
    <Head title="Quản lý Đơn hàng" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-display font-bold text-white">Quản lý Đơn hàng</h2>
        </template>
        <div class="py-6">
            <div class="mx-auto max-w-7xl px-6">
                <div class="mb-6 flex gap-4">
                    <div class="relative flex-1">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-admin-content-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input v-model="search" type="text" placeholder="Tìm mã đơn, tên, SĐT..." class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-white/10 bg-admin-surface/50 text-white placeholder-admin-content-muted text-sm focus:border-admin-accent/50 focus:ring-1 focus:ring-admin-accent/20 transition" />
                    </div>
                    <select v-model="status" class="rounded-xl border border-white/10 bg-admin-surface/50 text-admin-content text-sm px-4 py-2.5 focus:border-admin-accent/50 focus:ring-1 focus:ring-admin-accent/20">
                        <option value="">Tất cả</option>
                        <option v-for="(label, key) in statusLabels" :key="key" :value="key">{{ label }}</option>
                    </select>
                </div>
                <div class="overflow-hidden rounded-2xl border border-white/5 bg-admin-surface/50 backdrop-blur-sm">
                    <table class="min-w-full divide-y divide-white/5">
                        <thead class="bg-admin-surface-muted/50">
                            <tr>
                                <th class="px-6 py-3.5 text-left text-xs font-medium uppercase tracking-wider text-admin-content-muted">Mã đơn</th>
                                <th class="px-6 py-3.5 text-left text-xs font-medium uppercase tracking-wider text-admin-content-muted">Khách hàng</th>
                                <th class="px-6 py-3.5 text-left text-xs font-medium uppercase tracking-wider text-admin-content-muted">SĐT</th>
                                <th class="px-6 py-3.5 text-left text-xs font-medium uppercase tracking-wider text-admin-content-muted">Tổng tiền</th>
                                <th class="px-6 py-3.5 text-left text-xs font-medium uppercase tracking-wider text-admin-content-muted">Trạng thái</th>
                                <th class="px-6 py-3.5 text-left text-xs font-medium uppercase tracking-wider text-admin-content-muted">Ngày tạo</th>
                                <th class="px-6 py-3.5 text-right text-xs font-medium uppercase tracking-wider text-admin-content-muted">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/[.03]">
                            <tr v-for="order in orders.data" :key="order.id" class="hover:bg-white/[.02] transition-colors">
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-mono font-medium text-admin-accent">{{ order.order_number }}</td>
                                <td class="px-6 py-4 text-sm text-white">{{ $fixText(order.customer_name) }}</td>
                                <td class="px-6 py-4 text-sm text-admin-content-muted">{{ order.customer_phone }}</td>
                                <td class="px-6 py-4 text-sm text-admin-accent font-semibold">{{ formatPrice(order.total_amount) }}</td>
                                <td class="px-6 py-4"><span :class="statusColors[order.status]" class="inline-flex rounded-lg px-2.5 py-0.5 text-xs font-medium border">{{ statusLabels[order.status] }}</span></td>
                                <td class="px-6 py-4 text-sm text-admin-content-muted">{{ new Date(order.created_at).toLocaleDateString('vi-VN') }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                    <Link :href="route('admin.orders.show', order.id)" class="text-admin-accent hover:text-admin-accent-hover transition-colors">Chi tiết</Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-if="orders.links?.length > 3" class="mt-6 flex justify-center gap-1">
                    <Link v-for="link in orders.links" :key="link.label" :href="link.url || undefined" :class="[link.active ? 'bg-admin-accent/20 text-admin-accent border-admin-accent/30' : 'text-admin-content-muted border-white/5 hover:bg-white/5 hover:text-white', !link.url ? 'opacity-30 cursor-not-allowed' : '']" class="px-3.5 py-1.5 rounded-lg text-sm border transition-colors" preserve-state>{{ paginationLabel(link.label) }}</Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
