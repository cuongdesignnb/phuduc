<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref, onMounted } from 'vue';

const props = defineProps({
    stats: Object,
    ordersByStatus: Object,
    monthlyRevenue: Array,
    recentOrders: Array,
    recentReviews: Array,
    topProducts: Array,
});

const formatPrice = (v) => new Intl.NumberFormat('vi-VN').format(v) + '₫';
const formatDate = (d) => new Date(d).toLocaleDateString('vi-VN');

const statusLabels = { pending: 'Chờ xử lý', processing: 'Đang xử lý', shipping: 'Đang giao', completed: 'Hoàn thành', cancelled: 'Đã hủy' };
const statusColors = {
    pending: 'bg-amber-500/10 text-amber-400 border-amber-500/20',
    processing: 'bg-blue-500/10 text-blue-400 border-blue-500/20',
    shipping: 'bg-purple-500/10 text-purple-400 border-purple-500/20',
    completed: 'bg-volt-500/10 text-volt-400 border-volt-500/20',
    cancelled: 'bg-red-500/10 text-red-400 border-red-500/20',
};

const statCards = computed(() => [
    { label: 'Tổng doanh thu', value: formatPrice(props.stats?.totalRevenue || 0), icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', color: 'volt', change: null },
    { label: 'Đơn hàng', value: props.stats?.totalOrders || 0, sub: `${props.stats?.pendingOrders || 0} chờ xử lý`, icon: 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z', color: 'industrial' },
    { label: 'Sản phẩm', value: props.stats?.totalProducts || 0, sub: `${props.stats?.activeProducts || 0} đang bán`, icon: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4', color: 'purple' },
    { label: 'Đánh giá', value: props.stats?.totalReviews || 0, sub: `${props.stats?.pendingReviews || 0} chờ duyệt`, icon: 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z', color: 'amber' },
]);

const colorMap = {
    volt: { bg: 'bg-volt-500/10', text: 'text-volt-400', border: 'border-volt-500/20', glow: 'shadow-volt-500/5' },
    industrial: { bg: 'bg-industrial-500/10', text: 'text-industrial-400', border: 'border-industrial-500/20', glow: 'shadow-industrial-500/5' },
    purple: { bg: 'bg-purple-500/10', text: 'text-purple-400', border: 'border-purple-500/20', glow: 'shadow-purple-500/5' },
    amber: { bg: 'bg-amber-500/10', text: 'text-amber-400', border: 'border-amber-500/20', glow: 'shadow-amber-500/5' },
};

// Chart calculations
const maxRevenue = computed(() => Math.max(...(props.monthlyRevenue || []).map(m => Number(m.revenue)), 1));
const monthLabels = { '01': 'Th1', '02': 'Th2', '03': 'Th3', '04': 'Th4', '05': 'Th5', '06': 'Th6', '07': 'Th7', '08': 'Th8', '09': 'Th9', '10': 'Th10', '11': 'Th11', '12': 'Th12' };
const getMonthLabel = (m) => monthLabels[m?.split('-')[1]] || m;

const totalStatusOrders = computed(() => Object.values(props.ordersByStatus || {}).reduce((a, b) => a + b, 0) || 1);
const statusColorsPie = { pending: '#FBBF24', processing: '#3B82F6', shipping: '#A855F7', completed: '#09DE52', cancelled: '#EF4444' };

// SVG donut chart
const donutSegments = computed(() => {
    const entries = Object.entries(props.ordersByStatus || {});
    let offset = 0;
    return entries.map(([status, count]) => {
        const pct = (count / totalStatusOrders.value) * 100;
        const segment = { status, count, pct, offset, color: statusColorsPie[status] || '#666' };
        offset += pct;
        return segment;
    });
});
</script>

<template>
    <Head title="Tổng quan" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-display font-bold text-white">Tổng quan</h2>
                    <p class="text-sm text-carbon-400 mt-1">Chào mừng trở lại! Đây là tóm tắt hoạt động kinh doanh.</p>
                </div>
                <div class="flex gap-2">
                    <span class="inline-flex items-center gap-1.5 text-xs text-carbon-400 bg-carbon-800/50 border border-white/5 rounded-lg px-3 py-1.5">
                        <span class="w-2 h-2 rounded-full bg-volt-500 animate-pulse"></span>
                        Hệ thống hoạt động
                    </span>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-6 space-y-6">
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div v-for="(card, i) in statCards" :key="i"
                        class="relative overflow-hidden rounded-2xl border border-white/5 bg-carbon-900/50 p-5 backdrop-blur-sm hover:border-white/10 transition-all duration-300 group">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm text-carbon-400 font-medium">{{ card.label }}</p>
                                <p class="text-2xl font-display font-bold text-white mt-1.5">{{ card.value }}</p>
                                <p v-if="card.sub" class="text-xs text-carbon-500 mt-1">{{ card.sub }}</p>
                            </div>
                            <div :class="[colorMap[card.color].bg, colorMap[card.color].border, 'p-2.5 rounded-xl border shadow-lg', colorMap[card.color].glow]">
                                <svg :class="['w-5 h-5', colorMap[card.color].text]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="card.icon" />
                                </svg>
                            </div>
                        </div>
                        <!-- Subtle gradient overlay -->
                        <div :class="['absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none', `bg-gradient-to-br from-${card.color}-500/5 to-transparent`]"></div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Revenue Chart -->
                    <div class="lg:col-span-2 rounded-2xl border border-white/5 bg-carbon-900/50 p-6 backdrop-blur-sm">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-display font-semibold text-white">Doanh thu theo tháng</h3>
                            <span class="text-xs text-carbon-500">6 tháng gần nhất</span>
                        </div>
                        <div v-if="monthlyRevenue?.length" class="flex items-end gap-3 h-48">
                            <div v-for="m in monthlyRevenue" :key="m.month" class="flex-1 flex flex-col items-center gap-2">
                                <span class="text-xs text-carbon-500 font-medium">{{ formatPrice(m.revenue) }}</span>
                                <div class="w-full relative rounded-t-lg overflow-hidden bg-carbon-800 flex-1">
                                    <div
                                        class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-volt-600 to-volt-400 rounded-t-lg transition-all duration-700 ease-out"
                                        :style="{ height: (Number(m.revenue) / maxRevenue * 100) + '%', minHeight: '4px' }"
                                    ></div>
                                </div>
                                <div class="text-center">
                                    <span class="text-xs text-carbon-400 font-medium">{{ getMonthLabel(m.month) }}</span>
                                    <p class="text-[10px] text-carbon-600">{{ m.orders }} đơn</p>
                                </div>
                            </div>
                        </div>
                        <div v-else class="h-48 flex items-center justify-center text-carbon-600 text-sm">
                            Chưa có dữ liệu doanh thu
                        </div>
                    </div>

                    <!-- Order Status Donut -->
                    <div class="rounded-2xl border border-white/5 bg-carbon-900/50 p-6 backdrop-blur-sm">
                        <h3 class="text-lg font-display font-semibold text-white mb-6">Trạng thái đơn hàng</h3>
                        <div class="flex flex-col items-center">
                            <!-- SVG Donut -->
                            <svg viewBox="0 0 36 36" class="w-36 h-36 mb-4">
                                <circle cx="18" cy="18" r="15.9155" fill="none" stroke="#1a1a1a" stroke-width="3" />
                                <circle
                                    v-for="seg in donutSegments" :key="seg.status"
                                    cx="18" cy="18" r="15.9155" fill="none"
                                    :stroke="seg.color" stroke-width="3"
                                    :stroke-dasharray="seg.pct + ' ' + (100 - seg.pct)"
                                    :stroke-dashoffset="25 - seg.offset"
                                    stroke-linecap="round"
                                    class="transition-all duration-700"
                                />
                                <text x="18" y="17" text-anchor="middle" class="fill-white text-[5px] font-bold">{{ stats?.totalOrders || 0 }}</text>
                                <text x="18" y="21" text-anchor="middle" class="fill-carbon-500 text-[2.5px]">đơn hàng</text>
                            </svg>
                            <!-- Legend -->
                            <div class="w-full space-y-2">
                                <div v-for="seg in donutSegments" :key="seg.status" class="flex items-center justify-between text-sm">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full" :style="{ backgroundColor: seg.color }"></span>
                                        <span class="text-carbon-300 text-xs">{{ statusLabels[seg.status] || seg.status }}</span>
                                    </div>
                                    <span class="text-white text-xs font-semibold">{{ seg.count }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bottom Row -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Recent Orders -->
                    <div class="lg:col-span-2 rounded-2xl border border-white/5 bg-carbon-900/50 p-6 backdrop-blur-sm">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-display font-semibold text-white">Đơn hàng gần đây</h3>
                            <Link :href="route('admin.orders.index')" class="text-xs text-volt-400 hover:text-volt-300 transition-colors">Xem tất cả →</Link>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-white/5">
                                        <th class="text-left py-3 px-2 text-xs text-carbon-500 uppercase font-medium">Mã đơn</th>
                                        <th class="text-left py-3 px-2 text-xs text-carbon-500 uppercase font-medium">Khách hàng</th>
                                        <th class="text-left py-3 px-2 text-xs text-carbon-500 uppercase font-medium">Tổng tiền</th>
                                        <th class="text-left py-3 px-2 text-xs text-carbon-500 uppercase font-medium">Trạng thái</th>
                                        <th class="text-right py-3 px-2 text-xs text-carbon-500 uppercase font-medium">Ngày</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="order in recentOrders" :key="order.id" class="border-b border-white/[.03] hover:bg-white/[.02] transition-colors">
                                        <td class="py-3 px-2">
                                            <Link :href="route('admin.orders.show', order.id)" class="text-industrial-400 hover:text-industrial-300 font-mono text-xs">{{ order.order_number }}</Link>
                                        </td>
                                        <td class="py-3 px-2 text-white">{{ order.customer_name }}</td>
                                        <td class="py-3 px-2 text-volt-400 font-semibold">{{ formatPrice(order.total_amount) }}</td>
                                        <td class="py-3 px-2">
                                            <span :class="[statusColors[order.status], 'inline-flex rounded-lg px-2 py-0.5 text-xs font-medium border']">{{ statusLabels[order.status] }}</span>
                                        </td>
                                        <td class="py-3 px-2 text-right text-carbon-500 text-xs">{{ formatDate(order.created_at) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                            <div v-if="!recentOrders?.length" class="py-8 text-center text-carbon-600 text-sm">Chưa có đơn hàng nào</div>
                        </div>
                    </div>

                    <!-- Recent Reviews -->
                    <div class="rounded-2xl border border-white/5 bg-carbon-900/50 p-6 backdrop-blur-sm">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-display font-semibold text-white">Đánh giá mới</h3>
                            <Link :href="route('admin.reviews.index')" class="text-xs text-volt-400 hover:text-volt-300 transition-colors">Xem tất cả →</Link>
                        </div>
                        <div class="space-y-4">
                            <div v-for="review in recentReviews" :key="review.id" class="border-b border-white/5 pb-3 last:border-0 last:pb-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-sm font-medium text-white">{{ review.customer_name }}</span>
                                    <span class="text-amber-400 text-xs">{{ '★'.repeat(review.rating) }}{{ '☆'.repeat(5 - review.rating) }}</span>
                                </div>
                                <p class="text-xs text-carbon-400 line-clamp-2">{{ review.content }}</p>
                                <p class="text-[10px] text-carbon-600 mt-1">{{ review.product?.name }} · {{ formatDate(review.created_at) }}</p>
                            </div>
                            <div v-if="!recentReviews?.length" class="py-4 text-center text-carbon-600 text-sm">Chưa có đánh giá</div>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats Bar -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div class="rounded-2xl border border-white/5 bg-carbon-900/50 p-4 backdrop-blur-sm text-center">
                        <p class="text-2xl font-display font-bold text-white">{{ stats?.totalPosts || 0 }}</p>
                        <p class="text-xs text-carbon-500 mt-1">Bài viết</p>
                    </div>
                    <div class="rounded-2xl border border-white/5 bg-carbon-900/50 p-4 backdrop-blur-sm text-center">
                        <p class="text-2xl font-display font-bold text-white">{{ stats?.activeWarranties || 0 }}</p>
                        <p class="text-xs text-carbon-500 mt-1">Bảo hành đang hoạt động</p>
                    </div>
                    <div class="rounded-2xl border border-white/5 bg-carbon-900/50 p-4 backdrop-blur-sm text-center">
                        <p class="text-2xl font-display font-bold text-white">{{ stats?.activeProducts || 0 }}</p>
                        <p class="text-xs text-carbon-500 mt-1">Sản phẩm đang bán</p>
                    </div>
                    <div class="rounded-2xl border border-white/5 bg-carbon-900/50 p-4 backdrop-blur-sm text-center">
                        <p class="text-2xl font-display font-bold text-volt-400">{{ stats?.pendingOrders || 0 }}</p>
                        <p class="text-xs text-carbon-500 mt-1">Đơn cần xử lý</p>
                    </div>
                </div>

                <!-- Top Products -->
                <div v-if="topProducts?.length" class="rounded-2xl border border-white/5 bg-carbon-900/50 p-6 backdrop-blur-sm">
                    <h3 class="text-lg font-display font-semibold text-white mb-4">Sản phẩm nổi bật</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                        <div v-for="product in topProducts" :key="product.id" class="rounded-xl bg-carbon-800/50 border border-white/5 p-4 hover:border-white/10 transition-all">
                            <p class="text-sm font-medium text-white truncate">{{ product.name }}</p>
                            <p class="text-volt-400 font-semibold text-sm mt-1">{{ formatPrice(product.price) }}</p>
                            <div class="flex items-center justify-between mt-2 text-xs text-carbon-500">
                                <span>Kho: {{ product.stock }}</span>
                                <span>{{ product.reviews_count }} đánh giá</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
