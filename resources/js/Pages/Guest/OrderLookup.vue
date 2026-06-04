<script setup>
import GuestPageLayout from '@/Layouts/GuestPageLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({ order: Object, searched: Boolean });

const form = useForm({ order_number: '', customer_phone: '' });
const submit = () => form.post(route('order-lookup.lookup'));

const statusLabels = { pending: 'Chờ xử lý', processing: 'Đang xử lý', shipping: 'Đang giao hàng', completed: 'Hoàn thành', cancelled: 'Đã hủy' };
const statusColors = { pending: 'bg-amber-50 text-amber-700 border-amber-200', processing: 'bg-blue-50 text-blue-700 border-blue-200', shipping: 'bg-purple-50 text-purple-700 border-purple-200', completed: 'bg-emerald-50 text-emerald-700 border-emerald-200', cancelled: 'bg-red-50 text-red-700 border-red-200' };
const formatPrice = (p) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(p);
</script>

<template>
    <Head title="Tra cứu Đơn hàng" />
    <GuestPageLayout>
        <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8 py-16">
            <h1 class="text-2xl font-display font-bold text-ink-primary text-center mb-8">Tra cứu Đơn hàng</h1>

            <form @submit.prevent="submit" class="glass-card p-6 space-y-4">
                <div>
                    <label class="block text-sm text-ink-secondary mb-1">Mã đơn hàng</label>
                    <input v-model="form.order_number" type="text" placeholder="ORD-..." required class="w-full px-4 py-2.5 bg-white border border-surface-border rounded-xl text-sm text-ink-primary placeholder-ink-light focus:outline-none focus:border-brand-primary focus:ring-1 focus:ring-brand-primary/20 transition" />
                </div>
                <div>
                    <label class="block text-sm text-ink-secondary mb-1">Số điện thoại</label>
                    <input v-model="form.customer_phone" type="text" required class="w-full px-4 py-2.5 bg-white border border-surface-border rounded-xl text-sm text-ink-primary placeholder-ink-light focus:outline-none focus:border-brand-primary focus:ring-1 focus:ring-brand-primary/20 transition" />
                </div>
                <button type="submit" :disabled="form.processing" class="btn-primary w-full disabled:opacity-50">Tra cứu</button>
            </form>

            <Transition enter-active-class="transition-all duration-300" enter-from-class="opacity-0 translate-y-4" enter-to-class="opacity-100 translate-y-0">
                <div v-if="searched && order" class="mt-8 glass-card p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-display font-bold text-ink-primary">{{ order.order_number }}</h3>
                        <span :class="statusColors[order.status]" class="inline-flex rounded-full px-3 py-1 text-xs font-semibold border">{{ statusLabels[order.status] }}</span>
                    </div>
                    <div class="space-y-2 text-sm text-ink-secondary">
                        <p>Ngày đặt: {{ new Date(order.created_at).toLocaleString('vi-VN') }}</p>
                        <div class="border-t border-surface-border pt-3 mt-3">
                            <div v-for="item in order.items" :key="item.id" class="flex justify-between py-1">
                                <span class="text-ink-secondary">{{ item.product_name }} × {{ item.quantity }}</span>
                                <span class="font-medium text-ink-primary">{{ formatPrice(item.total) }}</span>
                            </div>
                        </div>
                        <div class="border-t border-surface-border pt-3 flex justify-between">
                            <strong class="text-ink-primary">Tổng cộng:</strong>
                            <span class="text-lg font-display font-bold text-brand-hover">{{ formatPrice(order.total_amount) }}</span>
                        </div>
                    </div>
                </div>
            </Transition>

            <div v-if="searched && !order" class="mt-8 glass-card border-red-500/20 p-6 text-center">
                <p class="text-red-500">Không tìm thấy đơn hàng. Vui lòng kiểm tra lại mã đơn và số điện thoại.</p>
            </div>
        </div>
    </GuestPageLayout>
</template>
