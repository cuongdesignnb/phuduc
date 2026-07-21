<script setup>
import { computed } from 'vue';

const props = defineProps({
    modelValue: { type: String, default: '' },
    id: { type: String, required: true },
    compact: { type: Boolean, default: false },
});
const emit = defineEmits(['update:modelValue', 'submit']);
const value = computed({
    get: () => props.modelValue,
    set: (nextValue) => emit('update:modelValue', nextValue),
});
const submit = () => {
    const keyword = value.value.trim();
    if (keyword) emit('submit', keyword);
};
</script>

<template>
    <form class="flex w-full overflow-hidden rounded-lg border border-line bg-surface-card focus-within:border-brand-border" role="search" @submit.prevent="submit">
        <label :for="id" class="sr-only">Tìm kiếm sản phẩm</label>
        <input
            :id="id"
            v-model="value"
            type="search"
            name="search"
            autocomplete="off"
            class="min-w-0 flex-1 border-0 bg-transparent px-4 text-sm text-content-primary placeholder:text-content-muted focus:ring-0"
            :class="compact ? 'h-11' : 'h-12'"
            placeholder="Tìm sản phẩm theo tên hoặc mã..."
        >
        <button type="submit" class="grid min-w-12 place-items-center bg-brand text-brand-contrast transition hover:bg-brand-hover" aria-label="Tìm kiếm sản phẩm">
            <svg class="h-5 w-5" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" />
            </svg>
        </button>
    </form>
</template>
