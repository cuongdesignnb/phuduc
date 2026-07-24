<script setup>
defineProps({ modelValue: { type: [String, Number], default: '' }, id: { type: String, default: '' }, type: { type: String, default: 'text' }, placeholder: { type: String, default: '' }, disabled: { type: Boolean, default: false } });
defineEmits(['update:modelValue']);
</script>

<template>
    <input :id="id" :type="type" :value="modelValue" :placeholder="placeholder" :disabled="disabled" class="w-full border border-admin-border bg-admin-page px-3 py-2 text-sm text-admin-content placeholder:text-admin-content-muted focus:border-admin-focus focus:outline-none focus:ring-2 focus:ring-admin-focus disabled:cursor-not-allowed disabled:opacity-60" @input="$emit('update:modelValue', $event.target.value)" />
</template>
