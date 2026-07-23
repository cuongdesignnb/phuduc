<script setup>
import GuestPageLayout from '@/Layouts/GuestPageLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    items: { type: Array, default: () => [] },
    summary: { type: Object, default: () => ({}) },
    warnings: { type: Array, default: () => [] },
    checkout_intent: { type: String, required: true },
    seo: { type: Object, default: () => ({}) },
});

const form = useForm({ checkout_intent: props.checkout_intent, customer_name: '', customer_phone: '', customer_email: '', shipping_address: '', notes: '' });
const submit = () => form.post(route('checkout.store'), { preserveScroll: true });
</script>

<template>
    <Head v-bind="seo" />
    <GuestPageLayout>
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <h1 class="mb-8 text-2xl font-display font-bold text-content-primary">Thanh toán</h1>
            <div v-if="warnings.length" class="mb-6 space-y-2" role="status">
                <p v-for="warning in warnings" :key="warning" class="rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-800">{{ warning }}</p>
            </div>
            <p v-if="form.errors.cart" class="mb-6 rounded-lg border border-danger/30 bg-danger/10 px-4 py-3 text-sm text-danger" role="alert">{{ form.errors.cart }}</p>

            <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                <form class="space-y-4 rounded-lg border border-line bg-surface-card p-6 lg:col-span-2" @submit.prevent="submit">
                    <h2 class="text-lg font-display font-bold text-content-primary">Thông tin giao hàng</h2>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <label class="text-sm font-semibold text-content-secondary">Họ tên *<input v-model="form.customer_name" type="text" maxlength="255" required class="mt-2 w-full rounded-lg border border-line bg-surface-card px-3 py-2.5" :aria-invalid="!!form.errors.customer_name" /> <span v-if="form.errors.customer_name" class="mt-1 block text-danger">{{ form.errors.customer_name }}</span></label>
                        <label class="text-sm font-semibold text-content-secondary">Số điện thoại *<input v-model="form.customer_phone" type="tel" maxlength="20" required class="mt-2 w-full rounded-lg border border-line bg-surface-card px-3 py-2.5" :aria-invalid="!!form.errors.customer_phone" /> <span v-if="form.errors.customer_phone" class="mt-1 block text-danger">{{ form.errors.customer_phone }}</span></label>
                        <label class="text-sm font-semibold text-content-secondary md:col-span-2">Email<input v-model="form.customer_email" type="email" maxlength="255" class="mt-2 w-full rounded-lg border border-line bg-surface-card px-3 py-2.5" /> <span v-if="form.errors.customer_email" class="mt-1 block text-danger">{{ form.errors.customer_email }}</span></label>
                        <label class="text-sm font-semibold text-content-secondary md:col-span-2">Địa chỉ giao hàng *<textarea v-model="form.shipping_address" rows="3" maxlength="1000" required class="mt-2 w-full rounded-lg border border-line bg-surface-card px-3 py-2.5" :aria-invalid="!!form.errors.shipping_address" /> <span v-if="form.errors.shipping_address" class="mt-1 block text-danger">{{ form.errors.shipping_address }}</span></label>
                        <label class="text-sm font-semibold text-content-secondary md:col-span-2">Ghi chú<textarea v-model="form.notes" rows="3" maxlength="1000" class="mt-2 w-full rounded-lg border border-line bg-surface-card px-3 py-2.5" /></label>
                    </div>
                    <p v-if="form.errors.checkout_intent" class="text-sm text-danger" role="alert">{{ form.errors.checkout_intent }}</p>
                    <button type="submit" :disabled="form.processing" class="btn-primary min-h-11 w-full disabled:opacity-50">{{ form.processing ? 'Đang xử lý...' : 'Đặt hàng' }}</button>
                </form>

                <section class="h-fit rounded-lg border border-line bg-surface-card p-6" aria-labelledby="checkout-summary-title">
                    <h2 id="checkout-summary-title" class="mb-4 text-lg font-display font-bold text-content-primary">Đơn hàng của bạn</h2>
                    <div class="space-y-3">
                        <div v-for="item in items" :key="item.product_id" class="flex justify-between gap-4 text-sm">
                            <span class="text-content-secondary">{{ item.name }} × {{ item.quantity }}</span>
                            <span class="font-semibold text-content-primary">{{ item.line_total_display }}</span>
                        </div>
                    </div>
                    <div class="mt-4 flex justify-between border-t border-line pt-4"><span class="font-semibold text-content-secondary">Tổng cộng</span><span class="text-xl font-display font-bold text-brand-text">{{ summary.total_display }}</span></div>
                </section>
            </div>
        </div>
    </GuestPageLayout>
</template>
