<script setup>
import GuestPageLayout from '@/Layouts/GuestPageLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({
    items: { type: Array, default: () => [] },
    summary: { type: Object, default: () => ({}) },
    warnings: { type: Array, default: () => [] },
    seo: { type: Object, default: () => ({}) },
});

const updateQty = (productId, quantity) => router.patch(route('cart.update'), { product_id: productId, quantity }, { preserveScroll: true });
const removeItem = (productId) => router.delete(route('cart.remove'), { data: { product_id: productId }, preserveScroll: true });
const clearCart = () => router.post(route('cart.clear'), {}, { preserveScroll: true });
</script>

<template>
    <Head v-bind="seo" />
    <GuestPageLayout>
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <h1 class="mb-8 text-2xl font-display font-bold text-content-primary">Giỏ hàng</h1>
            <div v-if="warnings.length" class="mb-6 space-y-2" role="status">
                <p v-for="warning in warnings" :key="warning" class="rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-800">{{ warning }}</p>
            </div>

            <div v-if="items.length" class="space-y-6">
                <div class="overflow-hidden rounded-lg border border-line bg-surface-card">
                    <div v-for="item in items" :key="item.product_id" class="flex flex-wrap items-center gap-4 border-b border-line p-4 last:border-0 sm:flex-nowrap">
                        <img v-if="item.image_url" :src="item.image_url" :alt="item.name" class="h-16 w-16 rounded-lg border border-line object-cover" />
                        <div v-else class="h-16 w-16 rounded-lg border border-line bg-surface-muted" aria-hidden="true"></div>
                        <div class="min-w-0 flex-1">
                            <Link :href="route('products.show', item.slug)" class="text-sm font-semibold text-content-primary hover:text-brand-text">{{ item.name }}</Link>
                            <p class="mt-1 text-sm font-semibold text-brand-text">{{ item.unit_price_display }}</p>
                        </div>
                        <div class="flex items-center rounded-lg border border-line bg-surface-muted">
                            <button type="button" class="grid min-h-11 min-w-11 place-items-center" :disabled="item.quantity <= 1" :aria-label="`Giảm số lượng ${item.name}`" @click="updateQty(item.product_id, item.quantity - 1)">−</button>
                            <output class="min-w-10 text-center text-sm font-semibold" :aria-label="`Số lượng ${item.name}: ${item.quantity}`">{{ item.quantity }}</output>
                            <button type="button" class="grid min-h-11 min-w-11 place-items-center" :disabled="item.quantity >= Math.min(item.stock, 99)" :aria-label="`Tăng số lượng ${item.name}`" @click="updateQty(item.product_id, item.quantity + 1)">+</button>
                        </div>
                        <div class="w-32 text-right text-sm font-bold text-content-primary">{{ item.line_total_display }}</div>
                        <button type="button" class="grid min-h-11 min-w-11 place-items-center text-content-muted hover:text-danger" :aria-label="`Xóa ${item.name}`" @click="removeItem(item.product_id)">×</button>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-4 rounded-lg border border-line bg-surface-card p-6">
                    <span class="text-lg font-semibold text-content-secondary">Tổng cộng</span>
                    <span class="text-2xl font-display font-bold text-brand-text">{{ summary.total_display }}</span>
                </div>

                <div class="flex flex-wrap justify-end gap-4">
                    <button type="button" class="btn-outline" @click="clearCart">Xóa giỏ hàng</button>
                    <Link :href="route('products.index')" class="btn-outline">Tiếp tục mua</Link>
                    <Link :href="route('checkout.index')" class="btn-primary">Tiến hành thanh toán</Link>
                </div>
            </div>

            <div v-else class="py-24 text-center">
                <p class="mb-4 text-content-secondary">Giỏ hàng đang trống.</p>
                <Link :href="route('products.index')" class="text-brand-text hover:text-brand-hover">Xem sản phẩm</Link>
            </div>
        </div>
    </GuestPageLayout>
</template>
