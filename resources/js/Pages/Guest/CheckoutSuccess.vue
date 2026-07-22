<script setup>
import GuestPageLayout from '@/Layouts/GuestPageLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({ order: Object });
const formatPrice = (p) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(p);
</script>

<template>
    <Head title="Đặt hàng thành công" />
    <GuestPageLayout>
        <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8 py-16 text-center">
            <div class="storefront-card p-8">
                <div class="w-16 h-16 rounded-full bg-brand-primary/10 flex items-center justify-center mx-auto mb-5">
                    <svg class="w-8 h-8 text-brand-hover" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <h1 class="text-2xl font-display font-bold text-ink-primary mb-2">Đặt hàng thành công!</h1>
                <p class="text-ink-secondary mb-6">Mã đơn hàng của bạn: <span class="font-mono font-bold text-brand-hover">{{ order.order_number }}</span></p>

                <div class="text-left border-t border-surface-border pt-6 space-y-2 text-sm text-ink-secondary">
                    <p><strong class="text-ink-primary">Khách hàng:</strong> {{ order.customer_name }}</p>
                    <p><strong class="text-ink-primary">SĐT:</strong> {{ order.customer_phone }}</p>
                    <p><strong class="text-ink-primary">Địa chỉ:</strong> {{ order.shipping_address }}</p>
                    <p><strong class="text-ink-primary">Tổng tiền:</strong> <span class="text-brand-hover font-display font-bold">{{ formatPrice(order.total_amount) }}</span></p>
                </div>

                <div class="mt-8 flex justify-center gap-4">
                    <Link :href="route('products.index')" class="btn-outline text-sm">Tiếp tục mua</Link>
                    <Link :href="route('order-lookup.index')" class="btn-primary text-sm">Tra cứu đơn hàng</Link>
                </div>
            </div>
        </div>
    </GuestPageLayout>
</template>
