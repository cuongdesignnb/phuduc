<script setup>
import { computed, nextTick, ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import GuestPageLayout from '@/Layouts/GuestPageLayout.vue';
import SeoHead from '@/Components/SeoHead.vue';
import Breadcrumbs from '@/Components/Storefront/Breadcrumbs.vue';
import FormField from '@/Components/Storefront/FormField.vue';

const props = defineProps({ page: { type: Object, required: true } });
const formElement = ref(null);
const errorSummary = ref(null);
const fieldOrder = ['customer_name', 'customer_phone', 'customer_email', 'shipping_address', 'notes', 'checkout_intent', 'cart'];
const form = useForm({
    checkout_intent: props.page.checkout.intent,
    customer_name: '',
    customer_phone: '',
    customer_email: '',
    shipping_address: '',
    notes: '',
});

const errorFor = (field) => form.errors[field] || '';
const firstErrorKey = computed(() => fieldOrder.find((field) => errorFor(field)) || '');
const firstErrorMessage = computed(() => errorFor(firstErrorKey.value));

watch(() => Object.keys(form.errors).map((key) => `${key}:${form.errors[key]}`).join('|'), async () => {
    if (!firstErrorKey.value) return;

    await nextTick();
    const target = firstErrorKey.value === 'cart' || firstErrorKey.value === 'checkout_intent'
        ? errorSummary.value
        : formElement.value?.querySelector(`[name="${firstErrorKey.value}"]`);
    target?.focus();
});

const submit = () => form.post(route('checkout.store'), { preserveScroll: true });
</script>

<template>
    <SeoHead v-bind="page.seo" />
    <GuestPageLayout>
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <Breadcrumbs :items="page.breadcrumbs" class="mb-6" />
            <h1 class="mb-8 text-2xl font-display font-bold text-content-primary">Thanh toán</h1>

            <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                <form ref="formElement" class="space-y-4 rounded-lg border border-line bg-surface-card p-6 lg:col-span-2" novalidate @submit.prevent="submit">
                    <div v-if="firstErrorMessage" ref="errorSummary" tabindex="-1" role="alert" aria-live="polite" class="rounded-lg border border-danger/30 bg-danger/10 px-4 py-3 text-sm text-danger">
                        {{ firstErrorMessage }}
                    </div>
                    <h2 class="text-lg font-display font-bold text-content-primary">Thông tin giao hàng</h2>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <FormField id="checkout-customer-name" label="Họ tên" :error="errorFor('customer_name')" required>
                            <template #default="{ id, describedBy }">
                                <input :id="id" v-model="form.customer_name" name="customer_name" type="text" maxlength="255" :aria-required="true" :aria-describedby="describedBy" :aria-invalid="errorFor('customer_name') ? 'true' : undefined" class="mt-2 w-full rounded-lg border border-line bg-surface-card px-3 py-2.5" />
                            </template>
                        </FormField>
                        <FormField id="checkout-customer-phone" label="Số điện thoại" :error="errorFor('customer_phone')" required>
                            <template #default="{ id, describedBy }">
                                <input :id="id" v-model="form.customer_phone" name="customer_phone" type="tel" maxlength="20" :aria-required="true" :aria-describedby="describedBy" :aria-invalid="errorFor('customer_phone') ? 'true' : undefined" class="mt-2 w-full rounded-lg border border-line bg-surface-card px-3 py-2.5" />
                            </template>
                        </FormField>
                        <FormField id="checkout-customer-email" label="Email" :error="errorFor('customer_email')" class="md:col-span-2">
                            <template #default="{ id, describedBy }">
                                <input :id="id" v-model="form.customer_email" name="customer_email" type="email" maxlength="255" :aria-describedby="describedBy" :aria-invalid="errorFor('customer_email') ? 'true' : undefined" class="mt-2 w-full rounded-lg border border-line bg-surface-card px-3 py-2.5" />
                            </template>
                        </FormField>
                        <FormField id="checkout-shipping-address" label="Địa chỉ giao hàng" :error="errorFor('shipping_address')" required>
                            <template #default="{ id, describedBy }">
                                <textarea :id="id" v-model="form.shipping_address" name="shipping_address" rows="3" maxlength="1000" :aria-required="true" :aria-describedby="describedBy" :aria-invalid="errorFor('shipping_address') ? 'true' : undefined" class="mt-2 w-full rounded-lg border border-line bg-surface-card px-3 py-2.5" />
                            </template>
                        </FormField>
                        <FormField id="checkout-notes" label="Ghi chú" :error="errorFor('notes')">
                            <template #default="{ id, describedBy }">
                                <textarea :id="id" v-model="form.notes" name="notes" rows="3" maxlength="1000" :aria-describedby="describedBy" :aria-invalid="errorFor('notes') ? 'true' : undefined" class="mt-2 w-full rounded-lg border border-line bg-surface-card px-3 py-2.5" />
                            </template>
                        </FormField>
                    </div>
                    <button type="submit" :disabled="form.processing" class="btn-primary min-h-11 w-full disabled:opacity-50">{{ form.processing ? 'Đang xử lý...' : 'Đặt hàng' }}</button>
                </form>

                <section class="h-fit rounded-lg border border-line bg-surface-card p-6" aria-labelledby="checkout-summary-title">
                    <h2 id="checkout-summary-title" class="mb-4 text-lg font-display font-bold text-content-primary">Đơn hàng của bạn</h2>
                    <div v-if="page.checkout.cart.warnings.length" class="mb-4 space-y-2" role="status" aria-live="polite">
                        <p v-for="warning in page.checkout.cart.warnings" :key="warning" class="text-sm text-amber-800">{{ warning }}</p>
                    </div>
                    <div class="space-y-3">
                        <div v-for="item in page.checkout.cart.items" :key="item.product_id" class="flex justify-between gap-4 text-sm">
                            <span class="text-content-secondary">{{ item.name }} × {{ item.quantity }}</span>
                            <span class="font-semibold text-content-primary">{{ item.subtotal_display }}</span>
                        </div>
                    </div>
                    <div class="mt-4 flex justify-between border-t border-line pt-4"><span class="font-semibold text-content-secondary">Tổng cộng</span><span class="text-xl font-display font-bold text-brand-text">{{ page.checkout.cart.summary.total_display }}</span></div>
                </section>
            </div>
        </div>
    </GuestPageLayout>
</template>
