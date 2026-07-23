<script setup>
import { reactive, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import FormField from './FormField.vue';
import UiButton from './UiButton.vue';

const props = defineProps({
    filters: { type: Object, required: true },
    sortOptions: { type: Array, default: () => [] },
});

const form = reactive({
    search: props.filters.search || '',
    min_price: props.filters.min_price ?? '',
    max_price: props.filters.max_price ?? '',
    sort: props.filters.sort || 'latest',
});

watch(() => props.filters, (filters) => {
    form.search = filters.search || '';
    form.min_price = filters.min_price ?? '';
    form.max_price = filters.max_price ?? '';
    form.sort = filters.sort || 'latest';
}, { deep: true });

const normalized = () => ({
    search: form.search || undefined,
    min_price: form.min_price !== '' ? form.min_price : undefined,
    max_price: form.max_price !== '' ? form.max_price : undefined,
    sort: form.sort !== 'latest' ? form.sort : undefined,
});

const submit = () => {
    router.get(route('products.index'), normalized(), {
        preserveState: true,
        preserveScroll: false,
        replace: true,
    });
};

const clear = () => {
    form.search = '';
    form.min_price = '';
    form.max_price = '';
    form.sort = 'latest';
    router.get(route('products.index'), {}, { preserveState: true, preserveScroll: false, replace: true });
};
</script>

<template>
    <form class="storefront-card grid gap-4 p-5 lg:grid-cols-[1.4fr_1fr_1fr_1fr_auto_auto] lg:items-end" role="search" @submit.prevent="submit">
        <FormField id="product-search" label="Tim san pham">
            <template #default="{ id, describedBy }">
                <input :id="id" v-model="form.search" :aria-describedby="describedBy" type="search" maxlength="100" class="w-full rounded-lg border border-line bg-surface-card px-3 py-2.5 text-sm">
            </template>
        </FormField>
        <FormField id="min-price" label="Gia tu">
            <template #default="{ id, describedBy }">
                <input :id="id" v-model="form.min_price" :aria-describedby="describedBy" type="number" min="0" step="1" class="w-full rounded-lg border border-line bg-surface-card px-3 py-2.5 text-sm">
            </template>
        </FormField>
        <FormField id="max-price" label="Gia den">
            <template #default="{ id, describedBy }">
                <input :id="id" v-model="form.max_price" :aria-describedby="describedBy" type="number" min="0" step="1" class="w-full rounded-lg border border-line bg-surface-card px-3 py-2.5 text-sm">
            </template>
        </FormField>
        <FormField id="sort" label="Sap xep">
            <template #default="{ id, describedBy }">
                <select :id="id" v-model="form.sort" :aria-describedby="describedBy" class="w-full rounded-lg border border-line bg-surface-card px-3 py-2.5 text-sm">
                    <option v-for="option in sortOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
                </select>
            </template>
        </FormField>
        <UiButton type="submit">Ap dung</UiButton>
        <UiButton type="button" variant="outline" @click="clear">Xoa loc</UiButton>
    </form>
</template>
