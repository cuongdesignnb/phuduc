<script setup>
import UiButton from './UiButton.vue';

defineProps({
    title: { type: String, required: true },
    description: { type: String, default: '' },
    actionLabel: { type: String, default: '' },
    actionHref: { type: String, default: '' },
});
</script>

<template>
    <div class="storefront-card px-6 py-12 text-center">
        <h2 class="font-display text-2xl font-bold text-content-primary">{{ title }}</h2>
        <p v-if="description" class="mx-auto mt-2 max-w-xl text-content-secondary">{{ description }}</p>
        <UiButton v-if="actionLabel && actionHref" :href="actionHref" class="mt-6">{{ actionLabel }}</UiButton>
    </div>
</template>
