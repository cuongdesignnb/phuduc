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
            <h1 class="text-2xl font-display font-bold text-white mb-8">Giỏ hàng</h1>

            <div v-if="cart.length" class="space-y-6">
                <div class="glass-card overflow-hidden">
                    <div v-for="item in cart" :key="item.product_id" class="flex items-center gap-4 p-4 border-b border-white/[0.04] last:border-0">
                        <img v-if="item.image" :src="'/storage/' + item.image" class="w-16 h-16 object-cover rounded-lg border border-white/[0.06]" />
                        <div v-else class="w-16 h-16 bg-carbon-800 rounded-lg"></div>
                        <div class="flex-1">
                            <Link :href="route('products.show', item.slug)" class="text-sm font-medium text-white hover:text-volt-400 transition-colors">{{ item.name }}</Link>
                            <p class="text-sm text-volt-400 font-display font-bold mt-1">{{ formatPrice(item.price) }}</p>
                        </div>
                        <div class="flex items-center border border-white/[0.08] rounded-xl bg-carbon-900/50 overflow-hidden">
                            <button @click="updateQty(item.product_id, item.quantity - 1)" class="px-3 py-1.5 text-carbon-400 hover:text-volt-400 hover:bg-white/[0.03] transition-colors">−</button>
                            <span class="px-3 py-1.5 text-sm text-white font-display">{{ item.quantity }}</span>
                            <button @click="updateQty(item.product_id, item.quantity + 1)" class="px-3 py-1.5 text-carbon-400 hover:text-volt-400 hover:bg-white/[0.03] transition-colors">+</button>
                        </div>
                        <div class="text-sm font-display font-bold text-white w-28 text-right">{{ formatPrice(item.price * item.quantity) }}</div>
                        <button @click="removeItem(item.product_id)" class="text-carbon-500 hover:text-red-400 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>

                <div class="glass-card p-6 flex justify-between items-center">
                    <span class="text-lg font-medium text-carbon-300">Tổng cộng:</span>
                    <span class="text-2xl font-display font-bold text-volt-400">{{ formatPrice(total) }}</span>
                </div>

                <div class="flex justify-end gap-4">
                    <Link :href="route('products.index')" class="btn-outline">Tiếp tục mua</Link>
                    <Link :href="route('checkout.index')" class="btn-primary">Tiến hành thanh toán</Link>
                </div>
            </div>

            <div v-else class="text-center py-24">
                <svg class="w-16 h-16 mx-auto text-carbon-700 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="0.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                <p class="text-carbon-500 mb-4">Giỏ hàng trống.</p>
                <Link :href="route('products.index')" class="text-volt-400 hover:text-volt-300 font-medium transition-colors">Xem sản phẩm →</Link>
            </div>
        </div>
    </GuestPageLayout>
</template>
