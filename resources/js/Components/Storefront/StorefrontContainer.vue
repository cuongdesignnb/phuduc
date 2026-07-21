<script setup>
defineProps({
    as: { type: String, default: 'div' },
    size: { type: String, default: 'shell', validator: (value) => ['shell', 'content'].includes(value) },
});
</script>

<template>
    <component :is="as" :class="size === 'content' ? 'storefront-content' : 'storefront-shell'">
        <slot />
    </component>
</template>
