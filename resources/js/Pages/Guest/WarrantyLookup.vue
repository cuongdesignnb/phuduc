<script setup>
import GuestPageLayout from '@/Layouts/GuestPageLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({ searched: Boolean, warranty: { type: Object, default: null }, message: { type: String, default: null }, seo: { type: Object, default: () => ({}) } });
const form = useForm({ serial_number: '', customer_phone: '' });
const submit = () => form.post(route('warranty-lookup.lookup'));
</script>

<template>
    <Head v-bind="seo" />
    <GuestPageLayout>
        <div class="mx-auto max-w-2xl px-4 py-16 sm:px-6 lg:px-8">
            <h1 class="mb-8 text-center text-2xl font-display font-bold text-content-primary">Tra cứu bảo hành</h1>
            <form class="space-y-4 rounded-lg border border-line bg-surface-card p-6" @submit.prevent="submit">
                <label class="block text-sm font-semibold text-content-secondary">Mã serial<input v-model="form.serial_number" type="text" maxlength="255" placeholder="Nhập mã serial" required class="mt-2 w-full rounded-lg border border-line bg-surface-card px-3 py-2.5" :aria-invalid="!!form.errors.serial_number" /> <span v-if="form.errors.serial_number" class="mt-1 block text-danger">{{ form.errors.serial_number }}</span></label>
                <label class="block text-sm font-semibold text-content-secondary">Số điện thoại<input v-model="form.customer_phone" type="tel" maxlength="20" required class="mt-2 w-full rounded-lg border border-line bg-surface-card px-3 py-2.5" :aria-invalid="!!form.errors.customer_phone" /> <span v-if="form.errors.customer_phone" class="mt-1 block text-danger">{{ form.errors.customer_phone }}</span></label>
                <button type="submit" :disabled="form.processing" class="btn-primary min-h-11 w-full disabled:opacity-50">Tra cứu</button>
            </form>

            <section v-if="searched && warranty" class="mt-8 rounded-lg border border-line bg-surface-card p-6" aria-labelledby="warranty-result-title">
                <div class="flex flex-wrap items-center justify-between gap-3"><h2 id="warranty-result-title" class="font-display font-bold text-content-primary">{{ warranty.product_name }}</h2><span class="rounded-full border border-line px-3 py-1 text-xs font-semibold">{{ warranty.status_display }}</span></div>
                <div class="mt-4 space-y-3 text-sm text-content-secondary"><div class="flex justify-between gap-4"><span>Mã serial</span><span class="font-mono text-content-primary">{{ warranty.serial_number }}</span></div><div class="flex justify-between gap-4"><span>Ngày kích hoạt</span><span class="text-content-primary">{{ warranty.activation_date_display }}</span></div><div class="flex justify-between gap-4"><span>Ngày hết hạn</span><span class="text-content-primary">{{ warranty.expiration_date_display }}</span></div></div>
            </section>
            <p v-else-if="searched" class="mt-8 rounded-lg border border-danger/30 bg-danger/10 p-6 text-center text-danger" role="alert">{{ message }}</p>
        </div>
    </GuestPageLayout>
</template>
