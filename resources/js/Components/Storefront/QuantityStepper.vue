<script setup>
const props = defineProps({
    modelValue: { type: Number, default: 1 },
    min: { type: Number, default: 1 },
    max: { type: Number, default: 99 },
    label: { type: String, default: 'Số lượng' },
});
const emit = defineEmits(['update:modelValue']);
const update = (value) => emit('update:modelValue', Math.min(props.max, Math.max(props.min, value)));
</script>

<template>
    <div class="inline-flex items-center rounded-lg border border-line bg-surface-card" :aria-label="label">
        <button type="button" class="grid min-h-11 min-w-11 place-items-center" :disabled="modelValue <= min" :aria-label="`Giảm ${label.toLowerCase()}`" @click="update(modelValue - 1)">−</button>
        <output class="min-w-10 text-center font-semibold" :aria-label="`${label}: ${modelValue}`">{{ modelValue }}</output>
        <button type="button" class="grid min-h-11 min-w-11 place-items-center" :disabled="modelValue >= max" :aria-label="`Tăng ${label.toLowerCase()}`" @click="update(modelValue + 1)">+</button>
    </div>
</template>
