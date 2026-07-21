<script setup>
import GuestPageLayout from '@/Layouts/GuestPageLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const cart = computed(() => Object.values(page.props.cart || {}));
const total = computed(() => cart.value.reduce((sum, item) => sum + item.price * item.quantity, 0));

const formatPrice = (p) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(p);

const updateQty = (productId, qty) => {
    router.patch(route('cart.update'), { product_id: productId, quantity: qty }, { preserveScroll: true });
};

const removeItem = (productId) => {
    router.delete(route('cart.remove'), { data: { product_id: productId }, preserveScroll: true });
};
</script>

<template>
    <Head title="Giỏ hàng" />
    <GuestPageLayout>
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-12">
            <h1 class="text-2xl font-display font-bold text-ink-primary mb-8">Giỏ hàng</h1>

            <div v-if="cart.length" class="space-y-6">
                <div class="storefront-card overflow-hidden">
                    <div v-for="item in cart" :key="item.product_id" class="flex items-center gap-4 p-4 border-b border-surface-border last:border-0">
                        <img v-if="item.image" :src="'/storage/' + item.image" class="w-16 h-16 object-cover rounded-lg border border-surface-border" />
                        <div v-else class="w-16 h-16 bg-surface-muted rounded-lg border border-surface-border"></div>
                        <div class="flex-1">
                            <Link :href="route('products.show', item.slug)" class="text-sm font-medium text-ink-primary hover:text-brand-hover transition-colors">{{ item.name }}</Link>
                            <p class="text-sm text-brand-hover font-display font-bold mt-1">{{ formatPrice(item.price) }}</p>
                        </div>
                        <div class="flex items-center border border-surface-border rounded-xl bg-surface-muted overflow-hidden">
                            <button @click="updateQty(item.product_id, item.quantity - 1)" class="px-3 py-1.5 text-ink-secondary hover:text-brand-hover hover:bg-black/[0.02] transition-colors">−</button>
                            <span class="px-3 py-1.5 text-sm text-ink-primary font-display">{{ item.quantity }}</span>
                            <button @click="updateQty(item.product_id, item.quantity + 1)" class="px-3 py-1.5 text-ink-secondary hover:text-brand-hover hover:bg-black/[0.02] transition-colors">+</button>
                        </div>
                        <div class="text-sm font-display font-bold text-ink-primary w-28 text-right">{{ formatPrice(item.price * item.quantity) }}</div>
                        <button @click="removeItem(item.product_id)" class="text-ink-light hover:text-red-500 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>

                <div class="storefront-card p-6 flex justify-between items-center">
                    <span class="text-lg font-medium text-ink-secondary">Tổng cộng:</span>
                    <span class="text-2xl font-display font-bold text-brand-hover">{{ formatPrice(total) }}</span>
                </div>

                <div class="flex justify-end gap-4">
                    <Link :href="route('products.index')" class="btn-outline">Tiếp tục mua</Link>
                    <Link :href="route('checkout.index')" class="btn-primary">Tiến hành thanh toán</Link>
                </div>
            </div>

            <div v-else class="text-center py-24">
                <svg class="w-16 h-16 mx-auto text-ink-light mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="0.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                <p class="text-ink-secondary mb-4">Giỏ hàng trống.</p>
                <Link :href="route('products.index')" class="text-brand-hover hover:text-brand-primary font-medium transition-colors">Xem sản phẩm →</Link>
            </div>
        </div>
    </GuestPageLayout>
</template>
