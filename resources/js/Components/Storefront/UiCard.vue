<script setup>
defineProps({
    as: { type: String, default: 'div' },
    interactive: { type: Boolean, default: false },
    padded: { type: Boolean, default: true },
});
</script>

<template>
    <component
        :is="as"
        :class="[
            interactive ? 'storefront-card-interactive' : 'storefront-card',
            padded && 'p-5 sm:p-6',
        ]"
    >
        <slot />
    </component>
</template>
