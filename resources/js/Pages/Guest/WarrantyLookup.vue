<script setup>
import GuestPageLayout from '@/Layouts/GuestPageLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({ warranty: Object, searched: Boolean });

const form = useForm({ serial_number: '' });
const submit = () => form.post(route('warranty-lookup.lookup'));

const statusLabels = { active: 'Còn hiệu lực', expired: 'Hết hạn', voided: 'Đã hủy' };
const statusColors = { active: 'bg-emerald-50 text-emerald-700 border-emerald-200', expired: 'bg-gray-50 text-gray-600 border-gray-200', voided: 'bg-red-50 text-red-700 border-red-200' };
</script>

<template>
    <Head title="Tra cứu Bảo hành" />
    <GuestPageLayout>
        <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8 py-16">
            <h1 class="text-2xl font-display font-bold text-ink-primary text-center mb-8">Tra cứu Bảo hành</h1>

            <form @submit.prevent="submit" class="storefront-card p-6 space-y-4">
                <div>
                    <label class="block text-sm text-ink-secondary mb-1">Serial Number</label>
                    <input v-model="form.serial_number" type="text" placeholder="Nhập mã serial..." required class="w-full px-4 py-2.5 bg-white border border-surface-border rounded-xl text-sm text-ink-primary placeholder-ink-light focus:outline-none focus:border-brand-primary focus:ring-1 focus:ring-brand-primary/20 transition" />
                </div>
                <button type="submit" :disabled="form.processing" class="btn-primary w-full disabled:opacity-50">Tra cứu</button>
            </form>

            <Transition enter-active-class="transition-all duration-300" enter-from-class="opacity-0 translate-y-4" enter-to-class="opacity-100 translate-y-0">
                <div v-if="searched && warranty" class="mt-8 storefront-card p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-display font-bold text-ink-primary">{{ warranty.product_name }}</h3>
                        <span :class="statusColors[warranty.status]" class="inline-flex rounded-full px-3 py-1 text-xs font-semibold border">{{ statusLabels[warranty.status] }}</span>
                    </div>
                    <div class="space-y-3 text-sm text-ink-secondary">
                        <div class="flex justify-between"><span>Serial:</span><span class="font-mono text-ink-primary">{{ warranty.serial_number }}</span></div>
                        <div class="flex justify-between"><span>Ngày kích hoạt:</span><span class="text-ink-primary">{{ warranty.activation_date || '—' }}</span></div>
                        <div class="flex justify-between"><span>Ngày hết hạn:</span><span class="text-ink-primary">{{ warranty.expiration_date || '—' }}</span></div>
                    </div>
                </div>
            </Transition>

            <div v-if="searched && !warranty" class="mt-8 storefront-card border-red-500/20 p-6 text-center">
                <p class="text-red-500">Không tìm thấy thông tin bảo hành. Vui lòng kiểm tra lại mã serial.</p>
            </div>
        </div>
    </GuestPageLayout>
</template>
