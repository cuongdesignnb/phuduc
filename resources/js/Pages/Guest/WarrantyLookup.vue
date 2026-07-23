<script setup>
import { computed, nextTick, ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import GuestPageLayout from '@/Layouts/GuestPageLayout.vue';
import SeoHead from '@/Components/SeoHead.vue';
import Breadcrumbs from '@/Components/Storefront/Breadcrumbs.vue';
import FormField from '@/Components/Storefront/FormField.vue';

const props = defineProps({ page: { type: Object, required: true } });
const formElement = ref(null);
const resultRegion = ref(null);
const errorSummary = ref(null);
const form = useForm({ serial_number: '', customer_phone: '' });
const errorFor = (field) => form.errors[field] || '';
const firstErrorKey = computed(() => errorFor('serial_number') ? 'serial_number' : errorFor('customer_phone') ? 'customer_phone' : '');

watch(() => Object.keys(form.errors).map((key) => `${key}:${form.errors[key]}`).join('|'), async () => {
    if (!firstErrorKey.value) return;
    await nextTick();
    formElement.value?.querySelector(`[name="${firstErrorKey.value}"]`)?.focus();
});

watch(() => props.page.lookup.searched, async (searched) => {
    if (searched) {
        await nextTick();
        (resultRegion.value || errorSummary.value)?.focus();
    }
});

const submit = () => form.post(route('warranty-lookup.lookup'));
</script>

<template>
    <SeoHead v-bind="page.seo" />
    <GuestPageLayout>
        <div class="mx-auto max-w-2xl px-4 py-12 sm:px-6 lg:px-8">
            <Breadcrumbs :items="page.breadcrumbs" class="mb-6" />
            <h1 class="mb-8 text-2xl font-display font-bold text-content-primary">Tra cứu bảo hành</h1>
            <form ref="formElement" class="space-y-4 rounded-lg border border-line bg-surface-card p-6" novalidate @submit.prevent="submit">
                <FormField id="warranty-lookup-serial" label="Mã serial" :error="errorFor('serial_number')" required>
                    <template #default="{ id, describedBy }">
                        <input :id="id" v-model="form.serial_number" name="serial_number" type="text" maxlength="255" placeholder="Nhập mã serial" :aria-required="true" :aria-describedby="describedBy" :aria-invalid="errorFor('serial_number') ? 'true' : undefined" class="mt-2 w-full rounded-lg border border-line bg-surface-card px-3 py-2.5" />
                    </template>
                </FormField>
                <FormField id="warranty-lookup-phone" label="Số điện thoại" :error="errorFor('customer_phone')" required>
                    <template #default="{ id, describedBy }">
                        <input :id="id" v-model="form.customer_phone" name="customer_phone" type="tel" maxlength="20" :aria-required="true" :aria-describedby="describedBy" :aria-invalid="errorFor('customer_phone') ? 'true' : undefined" class="mt-2 w-full rounded-lg border border-line bg-surface-card px-3 py-2.5" />
                    </template>
                </FormField>
                <button type="submit" :disabled="form.processing" class="btn-primary min-h-11 w-full disabled:opacity-50">{{ form.processing ? 'Đang tra cứu...' : 'Tra cứu' }}</button>
            </form>

            <section v-if="page.lookup.searched && page.lookup.result" ref="resultRegion" tabindex="-1" role="status" aria-live="polite" aria-labelledby="warranty-result-title" class="mt-8 rounded-lg border border-line bg-surface-card p-6">
                <div class="flex flex-wrap items-center justify-between gap-3"><h2 id="warranty-result-title" class="font-display font-bold text-content-primary">{{ page.lookup.result.product_name }}</h2><span class="rounded-full border border-line px-3 py-1 text-xs font-semibold">{{ page.lookup.result.status_display }}</span></div>
                <div class="mt-4 space-y-3 text-sm text-content-secondary"><div class="flex justify-between gap-4"><span>Mã serial</span><span class="font-mono text-content-primary">{{ page.lookup.result.serial_number }}</span></div><div class="flex justify-between gap-4"><span>Ngày kích hoạt</span><span class="text-content-primary">{{ page.lookup.result.activation_date_display }}</span></div><div class="flex justify-between gap-4"><span>Ngày hết hạn</span><span class="text-content-primary">{{ page.lookup.result.expiration_date_display }}</span></div></div>
            </section>
            <p v-else-if="page.lookup.searched" ref="errorSummary" tabindex="-1" class="mt-8 rounded-lg border border-danger/30 bg-danger/10 p-6 text-center text-danger" role="alert" aria-live="polite">{{ page.lookup.message }}</p>
        </div>
    </GuestPageLayout>
</template>
