<script setup>
import StorefrontContainer from './StorefrontContainer.vue';

defineProps({
    eyebrow: { type: String, default: '' },
    title: { type: String, required: true },
    description: { type: String, default: '' },
});
</script>

<template>
    <section class="border-b border-line-subtle bg-surface-muted py-12 sm:py-16">
        <StorefrontContainer size="content">
            <p v-if="eyebrow" class="section-tag">{{ eyebrow }}</p>
            <h1 class="max-w-4xl font-display text-4xl font-bold tracking-tight text-content-primary sm:text-5xl">{{ title }}</h1>
            <p v-if="description" class="mt-4 max-w-2xl text-lg text-content-secondary">{{ description }}</p>
            <slot />
        </StorefrontContainer>
    </section>
</template>
