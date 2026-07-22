<script setup>
import GuestPageLayout from '@/Layouts/GuestPageLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({ cart: Array });
const total = computed(() => (props.cart || []).reduce((sum, item) => sum + item.price * item.quantity, 0));
const formatPrice = (p) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(p);

const form = useForm({
    customer_name: '',
    customer_phone: '',
    customer_email: '',
    shipping_address: '',
    notes: '',
});

const submit = () => form.post(route('checkout.store'));
</script>

<template>
    <Head title="Thanh toán" />
    <GuestPageLayout>
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-12">
            <h1 class="text-2xl font-display font-bold text-ink-primary mb-8">Thanh toán</h1>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <form @submit.prevent="submit" class="lg:col-span-2 storefront-card p-6 space-y-4">
                    <h3 class="text-lg font-display font-bold text-ink-primary">Thông tin giao hàng</h3>
                    <div class="storefront-divider"></div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-ink-secondary mb-1">Họ tên *</label>
                            <input v-model="form.customer_name" type="text" required class="w-full px-4 py-2.5 bg-white border border-surface-border rounded-xl text-sm text-ink-primary placeholder-ink-light focus:outline-none focus:border-brand-primary focus:ring-1 focus:ring-brand-primary/20 transition" />
                            <p v-if="form.errors.customer_name" class="mt-1 text-sm text-red-500">{{ form.errors.customer_name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm text-ink-secondary mb-1">Số điện thoại *</label>
                            <input v-model="form.customer_phone" type="text" required class="w-full px-4 py-2.5 bg-white border border-surface-border rounded-xl text-sm text-ink-primary placeholder-ink-light focus:outline-none focus:border-brand-primary focus:ring-1 focus:ring-brand-primary/20 transition" />
                            <p v-if="form.errors.customer_phone" class="mt-1 text-sm text-red-500">{{ form.errors.customer_phone }}</p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm text-ink-secondary mb-1">Email</label>
                            <input v-model="form.customer_email" type="email" class="w-full px-4 py-2.5 bg-white border border-surface-border rounded-xl text-sm text-ink-primary placeholder-ink-light focus:outline-none focus:border-brand-primary focus:ring-1 focus:ring-brand-primary/20 transition" />
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm text-ink-secondary mb-1">Địa chỉ giao hàng *</label>
                            <textarea v-model="form.shipping_address" rows="2" required class="w-full px-4 py-2.5 bg-white border border-surface-border rounded-xl text-sm text-ink-primary placeholder-ink-light focus:outline-none focus:border-brand-primary focus:ring-1 focus:ring-brand-primary/20 transition"></textarea>
                            <p v-if="form.errors.shipping_address" class="mt-1 text-sm text-red-500">{{ form.errors.shipping_address }}</p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm text-ink-secondary mb-1">Ghi chú</label>
                            <textarea v-model="form.notes" rows="2" class="w-full px-4 py-2.5 bg-white border border-surface-border rounded-xl text-sm text-ink-primary placeholder-ink-light focus:outline-none focus:border-brand-primary focus:ring-1 focus:ring-brand-primary/20 transition"></textarea>
                        </div>
                    </div>
                    <button type="submit" :disabled="form.processing" class="btn-primary w-full disabled:opacity-50">Đặt hàng</button>
                </form>

                <!-- Order Summary -->
                <div class="storefront-card p-6 h-fit">
                    <h3 class="text-lg font-display font-bold text-ink-primary mb-4">Đơn hàng của bạn</h3>
                    <div class="space-y-3">
                        <div v-for="item in cart" :key="item.product_id" class="flex justify-between text-sm">
                            <span class="text-ink-secondary">{{ item.name }} × {{ item.quantity }}</span>
                            <span class="text-ink-primary font-medium">{{ formatPrice(item.price * item.quantity) }}</span>
                        </div>
                    </div>
                    <div class="border-t border-surface-border mt-4 pt-4 flex justify-between">
                        <span class="font-medium text-ink-secondary">Tổng cộng</span>
                        <span class="text-xl font-display font-bold text-brand-hover">{{ formatPrice(total) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </GuestPageLayout>
</template>
