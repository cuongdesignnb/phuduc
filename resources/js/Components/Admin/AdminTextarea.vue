<script setup>
defineProps({ modelValue: { type: String, default: '' }, id: { type: String, default: '' }, rows: { type: Number, default: 5 }, placeholder: { type: String, default: '' } });
defineEmits(['update:modelValue']);
</script>

<template>
    <textarea :id="id" :rows="rows" :value="modelValue" :placeholder="placeholder" class="w-full border border-admin-border bg-admin-page px-3 py-2 text-sm text-admin-content placeholder:text-admin-content-muted focus:border-admin-focus focus:outline-none focus:ring-2 focus:ring-admin-focus" @input="$emit('update:modelValue', $event.target.value)" />
</template>
