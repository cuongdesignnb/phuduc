<script setup>
import { vReveal } from '@/Composables/useMotion';

defineProps({
    eyebrow: { type: String, default: '' },
    title: { type: String, default: '' },
    description: { type: String, default: '' },
    inverse: { type: Boolean, default: false },
});
</script>

<template>
    <div v-reveal class="min-w-0 max-w-2xl">
        <p v-if="eyebrow" class="mb-2 inline-flex items-center gap-2 font-display text-xs font-bold uppercase tracking-[0.18em]" :class="inverse ? 'text-brand' : 'text-brand-text'">
            <span class="h-px w-6 bg-current" aria-hidden="true" />{{ eyebrow }}
        </p>
        <h2 v-if="title" class="whitespace-pre-line font-display text-2xl font-bold leading-tight tracking-tight sm:text-3xl lg:text-[2.1rem]" :class="inverse ? 'text-content-inverse' : 'text-content-primary'">{{ title }}</h2>
        <p v-if="description" class="mt-3 max-w-xl text-sm leading-6 sm:text-base" :class="inverse ? 'text-content-inverse/70' : 'text-content-secondary'">{{ description }}</p>
    </div>
</template>
