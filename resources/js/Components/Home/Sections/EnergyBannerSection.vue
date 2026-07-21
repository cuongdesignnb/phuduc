<script setup>
import ResponsiveImage from '@/Components/Storefront/ResponsiveImage.vue';
import StorefrontContainer from '@/Components/Storefront/StorefrontContainer.vue';

defineProps({ section: { type: Object, required: true } });
</script>

<template>
    <section class="storefront-section-compact">
        <StorefrontContainer>
            <div class="relative grid items-center gap-8 overflow-hidden rounded-xl bg-surface-inverse px-6 py-8 text-content-inverse sm:px-9 lg:grid-cols-[minmax(0,1fr)_22rem] lg:px-12 lg:py-12">
                <div class="relative z-10">
                    <p v-if="section.heading.eyebrow" class="font-display text-sm font-bold uppercase tracking-[.18em] text-brand">{{ section.heading.eyebrow }}</p>
                    <h2 class="mt-2 font-display text-3xl font-bold sm:text-4xl">{{ section.heading.title }}</h2>
                    <p v-if="section.heading.description" class="mt-3 max-w-3xl leading-7 text-content-inverse/75">{{ section.heading.description }}</p>
                    <dl v-if="section.config?.stats?.length" class="mt-7 grid max-w-2xl grid-cols-2 gap-5 sm:flex sm:flex-wrap sm:gap-10">
                        <div v-for="(stat, index) in section.config.stats" :key="`${stat.label}-${index}`">
                            <dd class="font-display text-2xl font-bold text-brand sm:text-3xl">{{ stat.value }}</dd>
                            <dt class="mt-1 text-sm text-content-inverse/70">{{ stat.label }}</dt>
                        </div>
                    </dl>
                </div>
                <div v-if="section.config?.image_url" class="overflow-hidden rounded-xl border border-content-inverse/15 bg-surface-card/5 p-3">
                    <ResponsiveImage :src="section.config.image_url" :alt="section.heading.title" aspect="4/3" object-fit="contain" />
                </div>
                <div v-else class="absolute -right-16 -top-20 h-64 w-64 rounded-full bg-brand/15" aria-hidden="true" />
            </div>
        </StorefrontContainer>
    </section>
</template>
