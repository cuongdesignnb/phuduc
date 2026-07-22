<script setup>
import UiButton from './UiButton.vue';

defineProps({
    eyebrow: { type: String, default: '' },
    title: { type: String, default: '' },
    description: { type: String, default: '' },
    actionLabel: { type: String, default: '' },
    actionHref: { type: String, default: '' },
    align: { type: String, default: 'left', validator: (value) => ['left', 'center'].includes(value) },
});
</script>

<template>
    <div class="mb-7 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between" :class="align === 'center' && 'text-center sm:block'">
        <div :class="align === 'center' && 'mx-auto'">
            <p v-if="eyebrow" class="section-tag">{{ eyebrow }}</p>
            <h2 v-if="title" class="section-title">{{ title }}</h2>
            <p v-if="description" class="section-subtitle" :class="align === 'center' && 'mx-auto'">{{ description }}</p>
        </div>
        <UiButton v-if="actionLabel && actionHref" :href="actionHref" variant="ghost" size="sm">
            {{ actionLabel }}
            <span aria-hidden="true">→</span>
        </UiButton>
    </div>
</template>
