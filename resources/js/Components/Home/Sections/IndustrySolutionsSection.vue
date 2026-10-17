<script setup>
import { Link } from '@inertiajs/vue3';
import HomeHeading from '../HomeHeading.vue';
import StorefrontContainer from '@/Components/Storefront/StorefrontContainer.vue';
import StorefrontIcon from '@/Components/Storefront/StorefrontIcon.vue';
import { vReveal } from '@/Composables/useMotion';

defineProps({ section: { type: Object, required: true } });
</script>

<template>
    <section v-if="section.items.length" class="storefront-section bg-surface-page">
        <StorefrontContainer>
            <div class="mb-7 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <HomeHeading eyebrow="Giải pháp ứng dụng" :title="section.heading.title" />
            </div>
            <div class="no-scrollbar -mx-[var(--ds-page-gutter)] flex snap-x snap-mandatory gap-4 overflow-x-auto px-[var(--ds-page-gutter)] pb-2 lg:mx-0 lg:grid lg:grid-cols-5 lg:overflow-visible lg:px-0">
                <component
                    :is="item.url ? Link : 'article'"
                    v-for="(item, index) in section.items"
                    :key="item.id"
                    v-reveal="{ delay: index * 80 }"
                    :href="item.url || undefined"
                    class="group relative block aspect-[4/3] w-[70%] shrink-0 snap-start overflow-hidden rounded-2xl bg-surface-inverse shadow-card sm:w-[40%] lg:w-auto"
                >
                    <img v-if="item.image_url" :src="item.image_url" :alt="item.title" loading="lazy" decoding="async" class="absolute inset-0 h-full w-full object-cover transition duration-700 group-hover:scale-110">
                    <div v-else class="ev-dark-section absolute inset-0"><div class="ev-grid-bg absolute inset-0" /></div>
                    <div class="absolute inset-0 bg-gradient-to-t from-surface-inverse/85 via-surface-inverse/15 to-transparent" />
                    <div class="absolute inset-x-0 bottom-0 flex items-center justify-between gap-2 p-4">
                        <strong class="font-display text-sm font-bold leading-tight text-content-inverse sm:text-base">{{ item.title }}</strong>
                        <span v-if="item.url" class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-content-inverse text-content-primary transition group-hover:bg-brand group-hover:text-brand-contrast" aria-hidden="true">
                            <StorefrontIcon name="chevronRight" class="h-4 w-4" />
                        </span>
                    </div>
                    <span class="absolute inset-x-0 top-0 h-1 origin-left scale-x-0 bg-brand transition duration-500 group-hover:scale-x-100" aria-hidden="true" />
                </component>
            </div>
        </StorefrontContainer>
    </section>
</template>
