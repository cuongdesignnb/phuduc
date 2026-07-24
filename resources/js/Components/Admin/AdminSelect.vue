<script setup>
defineProps({ modelValue: { type: [String, Number], default: '' }, id: { type: String, default: '' }, options: { type: Array, default: () => [] } });
defineEmits(['update:modelValue']);
</script>

<template>
    <select :id="id" :value="modelValue" class="w-full border border-admin-border bg-admin-page px-3 py-2 text-sm text-admin-content focus:border-admin-focus focus:outline-none focus:ring-2 focus:ring-admin-focus" @change="$emit('update:modelValue', $event.target.value)">
        <option v-for="option in options" :key="option.key" :value="option.key">{{ option.label }}</option>
    </select>
</template>
