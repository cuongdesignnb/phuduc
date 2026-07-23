<script setup>
import { computed, nextTick, reactive, ref, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import FormField from './FormField.vue';
import UiButton from './UiButton.vue';

const props = defineProps({
    filters: { type: Object, required: true },
    sortOptions: { type: Array, default: () => [] },
});

const page = usePage();
const firstError = ref(null);
const formElement = ref(null);

const errors = computed(() => page.props.errors || {});
const fieldError = (name) => {
    const value = errors.value[name];

    return Array.isArray(value) ? value[0] : value || '';
};

const firstErrorMessage = () => fieldError('search') || fieldError('min_price') || fieldError('max_price') || fieldError('sort') || null;

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

watch(errors, async () => {
    firstError.value = firstErrorMessage();

    if (!firstError.value) return;

    await nextTick();
    formElement.value?.querySelector('[aria-invalid="true"]')?.focus();
}, { immediate: true });

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
    <form ref="formElement" class="storefront-card grid gap-4 p-5 lg:grid-cols-[1.4fr_1fr_1fr_1fr_auto_auto] lg:items-end" role="search" @submit.prevent="submit">
        <p v-if="firstError" id="product-filter-error-summary" role="alert" aria-live="polite" class="lg:col-span-6 rounded-lg border border-danger/30 bg-danger/10 px-3 py-2 text-sm text-danger">
            {{ firstError }}
        </p>

        <FormField id="product-search" label="Tìm sản phẩm" :error="fieldError('search')">
            <template #default="{ id, describedBy }">
                <input :id="id" v-model="form.search" :aria-describedby="describedBy" :aria-invalid="fieldError('search') ? 'true' : undefined" type="search" maxlength="100" class="w-full rounded-lg border border-line bg-surface-card px-3 py-2.5 text-sm">
            </template>
        </FormField>
        <FormField id="min-price" label="Giá từ" :error="fieldError('min_price')">
            <template #default="{ id, describedBy }">
                <input :id="id" v-model="form.min_price" :aria-describedby="describedBy" :aria-invalid="fieldError('min_price') ? 'true' : undefined" type="number" min="0" step="1" class="w-full rounded-lg border border-line bg-surface-card px-3 py-2.5 text-sm">
            </template>
        </FormField>
        <FormField id="max-price" label="Giá đến" :error="fieldError('max_price')">
            <template #default="{ id, describedBy }">
                <input :id="id" v-model="form.max_price" :aria-describedby="describedBy" :aria-invalid="fieldError('max_price') ? 'true' : undefined" type="number" min="0" step="1" class="w-full rounded-lg border border-line bg-surface-card px-3 py-2.5 text-sm">
            </template>
        </FormField>
        <FormField id="sort" label="Sắp xếp" :error="fieldError('sort')">
            <template #default="{ id, describedBy }">
                <select :id="id" v-model="form.sort" :aria-describedby="describedBy" :aria-invalid="fieldError('sort') ? 'true' : undefined" class="w-full rounded-lg border border-line bg-surface-card px-3 py-2.5 text-sm">
                    <option v-for="option in sortOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
                </select>
            </template>
        </FormField>
        <UiButton type="submit">Áp dụng</UiButton>
        <UiButton type="button" variant="outline" @click="clear">Xóa bộ lọc</UiButton>
    </form>
</template>
