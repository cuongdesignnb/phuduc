<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AdminDataCard from '@/Components/Admin/AdminDataCard.vue';
import AdminEmptyState from '@/Components/Admin/AdminEmptyState.vue';
import AdminPageHeader from '@/Components/Admin/AdminPageHeader.vue';
import AdminStatCard from '@/Components/Admin/AdminStatCard.vue';
import AdminStatusBadge from '@/Components/Admin/AdminStatusBadge.vue';
import AdminTable from '@/Components/Admin/AdminTable.vue';

const props = defineProps({ page: { type: Object, required: true } });
const dashboard = computed(() => props.page.dashboard || {});
const summary = computed(() => dashboard.value.summary || []);
const metric = (key) => summary.value.find((item) => item.key === key) || { display: '0', label: key };
const monthlyRevenue = computed(() => dashboard.value.monthly_revenue || []);
const ordersByStatus = computed(() => dashboard.value.orders_by_status || []);
const recentOrders = computed(() => dashboard.value.recent_orders || []);
const recentReviews = computed(() => dashboard.value.recent_reviews || []);
const topProducts = computed(() => dashboard.value.top_products || []);
</script>

<template>
    <Head :title="page.meta?.title || 'Tổng quan'" />
    <AuthenticatedLayout>
        <template #header>
            <AdminPageHeader :title="page.meta?.title || 'Tổng quan'" description="Theo dõi các chỉ số vận hành chính của cửa hàng." />
        </template>

        <div class="space-y-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <AdminStatCard :label="metric('total_orders').label" :value="metric('total_orders').display" />
                <AdminStatCard :label="metric('revenue').label" :value="metric('revenue').display" />
                <AdminStatCard :label="metric('active_products').label" :value="metric('active_products').display" />
                <AdminStatCard :label="metric('pending_orders').label" :value="metric('pending_orders').display" tone="amber" />
            </div>

            <div class="grid min-w-0 grid-cols-1 gap-6 xl:grid-cols-3">
                <AdminDataCard title="Doanh thu 6 tháng gần nhất" class="xl:col-span-2">
                    <div v-if="monthlyRevenue.length" class="grid h-56 grid-cols-6 items-end gap-2 sm:gap-4" aria-label="Biểu đồ doanh thu theo tháng">
                        <div v-for="month in monthlyRevenue" :key="month.month" class="flex min-w-0 flex-col items-center gap-2">
                            <span class="max-w-full truncate text-[10px] text-carbon-400 sm:text-xs">{{ month.revenue_display }}</span>
                            <div class="flex h-36 w-full items-end rounded-t bg-carbon-800">
                                <div class="w-full rounded-t bg-volt-400" :style="{ height: `${Math.max(month.percentage, month.revenue ? 4 : 1)}%` }" :title="`${month.label}: ${month.revenue_display}`" />
                            </div>
                            <span class="text-[10px] text-carbon-400 sm:text-xs">{{ month.label }}</span>
                        </div>
                    </div>
                    <AdminEmptyState v-else message="Chưa có dữ liệu doanh thu." />
                </AdminDataCard>

                <AdminDataCard title="Trạng thái đơn hàng">
                    <div v-if="ordersByStatus.length" class="space-y-3">
                        <div v-for="item in ordersByStatus" :key="item.key" class="flex items-center justify-between gap-3">
                            <span class="min-w-0 truncate text-sm text-carbon-300">{{ item.label }}</span>
                            <span class="shrink-0 font-semibold text-white">{{ item.count }}</span>
                        </div>
                    </div>
                    <AdminEmptyState v-else message="Chưa có đơn hàng." />
                </AdminDataCard>
            </div>

            <div class="grid min-w-0 grid-cols-1 gap-6 xl:grid-cols-2">
                <AdminDataCard title="Đơn hàng gần đây">
                    <template #default>
                        <AdminTable :columns="[{ key: 'order', label: 'Mã đơn' }, { key: 'customer', label: 'Khách hàng' }, { key: 'total', label: 'Tổng tiền' }, { key: 'status', label: 'Trạng thái' }, { key: 'date', label: 'Ngày' }]">
                            <tr v-for="order in recentOrders" :key="order.id" class="border-b border-white/5">
                                <td class="px-3 py-3"><Link :href="order.admin_url" class="font-medium text-volt-300 hover:text-volt-200 focus:outline-none focus:ring-2 focus:ring-volt-400">{{ order.order_number }}</Link></td>
                                <td class="max-w-36 truncate px-3 py-3 text-carbon-200">{{ order.customer_name }}</td>
                                <td class="whitespace-nowrap px-3 py-3 text-carbon-200">{{ order.total.display }}</td>
                                <td class="px-3 py-3"><AdminStatusBadge :status="order.status" /></td>
                                <td class="whitespace-nowrap px-3 py-3 text-carbon-400">{{ order.created_at_display }}</td>
                            </tr>
                        </AdminTable>
                        <AdminEmptyState v-if="!recentOrders.length" message="Chưa có đơn hàng nào." />
                        <Link :href="route('admin.orders.index')" class="mt-4 inline-flex text-sm text-volt-300 hover:text-volt-200 focus:outline-none focus:ring-2 focus:ring-volt-400">Xem tất cả đơn hàng</Link>
                    </template>
                </AdminDataCard>

                <AdminDataCard title="Đánh giá gần đây">
                    <div v-if="recentReviews.length" class="divide-y divide-white/5">
                        <article v-for="review in recentReviews" :key="review.id" class="flex items-start justify-between gap-4 py-3 first:pt-0 last:pb-0">
                            <div class="min-w-0">
                                <p class="truncate font-medium text-white">{{ review.reviewer_name }}</p>
                                <p class="truncate text-sm text-carbon-400">{{ review.product_name || 'Sản phẩm đã ẩn' }}</p>
                                <p class="mt-1 text-xs text-carbon-500">{{ review.created_at_display }} · {{ review.rating }}/5</p>
                            </div>
                            <AdminStatusBadge :status="review.status" />
                        </article>
                    </div>
                    <AdminEmptyState v-else message="Chưa có đánh giá nào." />
                    <Link :href="route('admin.reviews.index')" class="mt-4 inline-flex text-sm text-volt-300 hover:text-volt-200 focus:outline-none focus:ring-2 focus:ring-volt-400">Xem tất cả đánh giá</Link>
                </AdminDataCard>
            </div>

            <AdminDataCard title="Sản phẩm nổi bật">
                <div v-if="topProducts.length" class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-5">
                    <Link v-for="product in topProducts" :key="product.id" :href="product.admin_url" class="min-w-0 border border-white/10 p-3 hover:border-volt-400/50 focus:outline-none focus:ring-2 focus:ring-volt-400">
                        <p class="truncate font-medium text-white">{{ product.name }}</p>
                        <p class="mt-1 text-sm text-volt-300">{{ product.price.display }}</p>
                        <p class="mt-2 text-xs text-carbon-400">Kho: {{ product.stock }} · {{ product.review_count }} đánh giá</p>
                    </Link>
                </div>
                <AdminEmptyState v-else message="Chưa có sản phẩm nổi bật." />
            </AdminDataCard>

            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                <AdminStatCard :label="metric('total_products').label" :value="metric('total_products').display" />
                <AdminStatCard :label="metric('total_reviews').label" :value="metric('total_reviews').display" />
                <AdminStatCard :label="metric('published_posts').label" :value="metric('published_posts').display" />
                <AdminStatCard :label="metric('active_warranties').label" :value="metric('active_warranties').display" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
