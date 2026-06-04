<script setup>
import GuestPageLayout from '@/Layouts/GuestPageLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({ order: Object, searched: Boolean });

const form = useForm({ order_number: '', customer_phone: '' });
const submit = () => form.post(route('order-lookup.lookup'));

const statusLabels = { pending: 'Chờ xử lý', processing: 'Đang xử lý', shipping: 'Đang giao hàng', completed: 'Hoàn thành', cancelled: 'Đã hủy' };
const statusColors = { pending: 'bg-yellow-500/10 text-yellow-400 border-yellow-500/20', processing: 'bg-industrial-500/10 text-industrial-400 border-industrial-500/20', shipping: 'bg-purple-500/10 text-purple-400 border-purple-500/20', completed: 'bg-volt-500/10 text-volt-400 border-volt-500/20', cancelled: 'bg-red-500/10 text-red-400 border-red-500/20' };
const formatPrice = (p) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(p);
</script>

<template>
    <Head title="Tra cứu Đơn hàng" />
    <GuestPageLayout>
        <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8 py-16">
            <h1 class="text-2xl font-display font-bold text-white text-center mb-8">Tra cứu Đơn hàng</h1>

            <form @submit.prevent="submit" class="glass-card p-6 space-y-4">
                <div>
                    <label class="block text-sm text-carbon-400 mb-1">Mã đơn hàng</label>
                    <input v-model="form.order_number" type="text" placeholder="ORD-..." required class="w-full px-4 py-2.5 bg-carbon-900/50 border border-white/[0.06] rounded-xl text-sm text-white placeholder-carbon-500 focus:outline-none focus:border-volt-500/40 focus:ring-1 focus:ring-volt-500/20 transition" />
                </div>
                <div>
                    <label class="block text-sm text-carbon-400 mb-1">Số điện thoại</label>
                    <input v-model="form.customer_phone" type="text" required class="w-full px-4 py-2.5 bg-carbon-900/50 border border-white/[0.06] rounded-xl text-sm text-white placeholder-carbon-500 focus:outline-none focus:border-volt-500/40 focus:ring-1 focus:ring-volt-500/20 transition" />
                </div>
                <button type="submit" :disabled="form.processing" class="btn-primary w-full disabled:opacity-50">Tra cứu</button>
            </form>

            <Transition enter-active-class="transition-all duration-300" enter-from-class="opacity-0 translate-y-4" enter-to-class="opacity-100 translate-y-0">
                <div v-if="searched && order" class="mt-8 glass-card p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-display font-bold text-white">{{ order.order_number }}</h3>
                        <span :class="statusColors[order.status]" class="inline-flex rounded-full px-3 py-1 text-xs font-semibold border">{{ statusLabels[order.status] }}</span>
                    </div>
                    <div class="space-y-2 text-sm text-carbon-300">
                        <p>Ngày đặt: {{ new Date(order.created_at).toLocaleString('vi-VN') }}</p>
                        <div class="border-t border-white/[0.06] pt-3 mt-3">
                            <div v-for="item in order.items" :key="item.id" class="flex justify-between py-1">
                                <span class="text-carbon-400">{{ item.product_name }} × {{ item.quantity }}</span>
                                <span class="font-medium text-white">{{ formatPrice(item.total) }}</span>
                            </div>
                        </div>
                        <div class="border-t border-white/[0.06] pt-3 flex justify-between">
                            <strong class="text-white">Tổng cộng:</strong>
                            <span class="text-lg font-display font-bold text-volt-400">{{ formatPrice(order.total_amount) }}</span>
                        </div>
                    </div>
                </div>
            </Transition>

            <div v-if="searched && !order" class="mt-8 glass-card border-red-500/20 p-6 text-center">
                <p class="text-red-400">Không tìm thấy đơn hàng. Vui lòng kiểm tra lại mã đơn và số điện thoại.</p>
            </div>
        </div>
    </GuestPageLayout>
</template>
