<script setup>
import GuestPageLayout from '@/Layouts/GuestPageLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({ order: { type: Object, required: true }, seo: { type: Object, default: () => ({}) } });
</script>

<template>
    <Head v-bind="seo" />
    <GuestPageLayout>
        <div class="mx-auto max-w-2xl px-4 py-16 sm:px-6 lg:px-8">
            <section class="rounded-lg border border-line bg-surface-card p-8" aria-labelledby="success-title">
                <h1 id="success-title" class="text-2xl font-display font-bold text-content-primary">Đặt hàng thành công</h1>
                <p class="mt-2 text-content-secondary">Mã đơn hàng: <strong class="font-mono text-brand-text">{{ order.order_number }}</strong></p>
                <div class="mt-6 space-y-2 border-t border-line pt-6 text-sm text-content-secondary">
                    <p><strong class="text-content-primary">Khách hàng:</strong> {{ order.customer_name }}</p>
                    <p><strong class="text-content-primary">Số điện thoại:</strong> {{ order.customer_phone }}</p>
                    <p><strong class="text-content-primary">Địa chỉ:</strong> {{ order.shipping_address }}</p>
                    <p><strong class="text-content-primary">Tổng tiền:</strong> <span class="font-bold text-brand-text">{{ order.total_display }}</span></p>
                </div>
                <div class="mt-8 flex flex-wrap gap-4">
                    <Link :href="route('products.index')" class="btn-outline">Tiếp tục mua</Link>
                    <Link :href="route('order-lookup.index')" class="btn-primary">Tra cứu đơn hàng</Link>
                </div>
            </section>
        </div>
    </GuestPageLayout>
</template>
