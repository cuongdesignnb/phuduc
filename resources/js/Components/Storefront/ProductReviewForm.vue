<script setup>
import { useForm } from '@inertiajs/vue3';
import FormField from './FormField.vue';
import UiButton from './UiButton.vue';

const props = defineProps({
    productId: { type: Number, required: true },
});

const form = useForm({
    product_id: props.productId,
    customer_name: '',
    customer_email: '',
    customer_phone: '',
    rating: 5,
    content: '',
});

const submit = () => {
    form.post(route('reviews.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <form class="storefront-card space-y-4 p-5" @submit.prevent="submit">
        <div class="grid gap-4 md:grid-cols-3">
            <FormField id="review-name" label="Ho ten" :error="form.errors.customer_name" required>
                <template #default="{ id, describedBy }">
                    <input :id="id" v-model="form.customer_name" :aria-describedby="describedBy" type="text" class="w-full rounded-lg border border-line bg-surface-card px-3 py-2.5 text-sm">
                </template>
            </FormField>
            <FormField id="review-email" label="Email" :error="form.errors.customer_email">
                <template #default="{ id, describedBy }">
                    <input :id="id" v-model="form.customer_email" :aria-describedby="describedBy" type="email" class="w-full rounded-lg border border-line bg-surface-card px-3 py-2.5 text-sm">
                </template>
            </FormField>
            <FormField id="review-phone" label="Dien thoai" :error="form.errors.customer_phone">
                <template #default="{ id, describedBy }">
                    <input :id="id" v-model="form.customer_phone" :aria-describedby="describedBy" type="tel" class="w-full rounded-lg border border-line bg-surface-card px-3 py-2.5 text-sm">
                </template>
            </FormField>
        </div>
        <fieldset>
            <legend class="mb-2 text-sm font-semibold text-content-primary">Danh gia</legend>
            <div class="flex flex-wrap gap-2" role="radiogroup" aria-label="Danh gia">
                <label v-for="rating in 5" :key="rating" class="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-line px-3 py-2 text-sm">
                    <input v-model="form.rating" type="radio" :value="rating">
                    {{ rating }}/5
                </label>
            </div>
        </fieldset>
        <FormField id="review-content" label="Noi dung" :error="form.errors.content" required>
            <template #default="{ id, describedBy }">
                <textarea :id="id" v-model="form.content" :aria-describedby="describedBy" rows="4" class="w-full rounded-lg border border-line bg-surface-card px-3 py-2.5 text-sm" />
            </template>
        </FormField>
        <UiButton type="submit" :disabled="form.processing">Gui danh gia</UiButton>
    </form>
</template>
