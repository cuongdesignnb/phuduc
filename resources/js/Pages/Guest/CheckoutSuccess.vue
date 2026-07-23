<script setup>
import { nextTick, onMounted, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import GuestPageLayout from '@/Layouts/GuestPageLayout.vue';
import SeoHead from '@/Components/SeoHead.vue';
import Breadcrumbs from '@/Components/Storefront/Breadcrumbs.vue';

const props = defineProps({ page: { type: Object, required: true } });
const resultRegion = ref(null);

onMounted(() => nextTick(() => resultRegion.value?.focus()));
</script>

<template>
    <SeoHead v-bind="page.seo" />
    <GuestPageLayout>
        <div class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
            <Breadcrumbs :items="page.breadcrumbs" class="mb-6" />
            <section ref="resultRegion" tabindex="-1" role="status" aria-live="polite" aria-labelledby="success-title" class="rounded-lg border border-line bg-surface-card p-8">
                <h1 id="success-title" class="text-2xl font-display font-bold text-content-primary">Đặt hàng thành công</h1>
                <div class="mt-6 space-y-2 border-t border-line pt-6 text-sm text-content-secondary">
                    <p><strong class="text-content-primary">Mã đơn hàng:</strong> <span class="font-mono text-brand-text">{{ page.order.order_number }}</span></p>
                    <p><strong class="text-content-primary">Trạng thái:</strong> {{ page.order.status_display }}</p>
                    <p><strong class="text-content-primary">Ngày đặt:</strong> {{ page.order.created_at_display }}</p>
                    <p><strong class="text-content-primary">Khách hàng:</strong> {{ page.order.customer.name }}</p>
                    <p><strong class="text-content-primary">Số điện thoại:</strong> {{ page.order.customer.phone_masked }}</p>
                </div>

                <div class="mt-6 border-t border-line pt-6">
                    <h2 class="font-display font-bold text-content-primary">Chi tiết đơn hàng</h2>
                    <div class="mt-3 space-y-2 text-sm">
                        <div v-for="(item, index) in page.order.items" :key="`${item.product_name}-${index}`" class="flex justify-between gap-4">
                            <span class="text-content-secondary">{{ item.product_name }} × {{ item.quantity }}<span class="block text-xs text-content-muted">{{ item.unit_price_display }} / sản phẩm</span></span>
                            <span class="font-semibold text-content-primary">{{ item.subtotal_display }}</span>
                        </div>
                    </div>
                    <div class="mt-4 flex justify-between border-t border-line pt-4"><strong class="text-content-primary">Tổng cộng</strong><span class="text-xl font-display font-bold text-brand-text">{{ page.order.total_display }}</span></div>
                </div>

                <div class="mt-8 flex flex-wrap gap-4">
                    <Link :href="route('products.index')" class="btn-outline">Tiếp tục mua</Link>
                    <Link :href="route('order-lookup.index')" class="btn-primary">Tra cứu đơn hàng</Link>
                </div>
            </section>
        </div>
    </GuestPageLayout>
</template>
