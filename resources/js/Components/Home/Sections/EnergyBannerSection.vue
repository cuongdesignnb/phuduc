<script setup>
import { computed } from 'vue';
import HomeHeading from '../HomeHeading.vue';
import StorefrontContainer from '@/Components/Storefront/StorefrontContainer.vue';
import StorefrontIcon from '@/Components/Storefront/StorefrontIcon.vue';
import { vCountUp, vReveal } from '@/Composables/useMotion';

const props = defineProps({
    section: { type: Object, required: true },
    // Featured products with photos, used when the banner has no image of its own.
    showcase: { type: Array, default: () => [] },
});
const image = computed(() => props.section.config?.image_url || (props.showcase[1] || props.showcase[0])?.image_url || '');
const stats = computed(() => (props.section.config?.stats || []).filter((stat) => stat?.value || stat?.label));
const statIcons = ['chart', 'battery', 'leaf', 'handshake', 'bolt', 'award'];
</script>

<template>
    <section class="relative overflow-hidden bg-surface-card">
        <StorefrontContainer>
            <div class="grid items-stretch gap-8 py-12 sm:py-16 lg:grid-cols-[minmax(0,1.1fr)_minmax(0,.9fr)] lg:gap-14">
                <div class="flex flex-col justify-center">
                    <HomeHeading :eyebrow="section.heading.eyebrow" :title="section.heading.title" :description="section.heading.description" />

                    <!-- EV charging indicator -->
                    <div v-reveal="{ delay: 150 }" class="mt-8 flex max-w-md items-center gap-4 rounded-2xl border border-line bg-surface-page p-4">
                        <span class="relative flex h-12 w-7 shrink-0 items-end overflow-hidden rounded-md border-2 border-success p-0.5" aria-hidden="true">
                            <span class="absolute -top-1.5 left-1/2 h-1 w-3 -translate-x-1/2 rounded-sm bg-success" />
                            <span class="ev-charge-fill block h-full w-full rounded-sm bg-success" />
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-sm font-bold text-content-primary">Năng lượng sạch · Vận hành êm ái</span>
                            <span class="mt-2 block h-1.5 overflow-hidden rounded-full bg-line-subtle">
                                <span class="ev-flow-line block h-full w-full" />
                            </span>
                        </span>
                        <StorefrontIcon name="leaf" class="h-6 w-6 shrink-0 text-success" />
                    </div>
                </div>

                <div v-reveal="{ from: 'right' }" class="relative min-h-72 overflow-hidden rounded-3xl bg-surface-inverse">
                    <img v-if="image" :src="image" :alt="section.heading.title" loading="lazy" decoding="async" class="ev-kenburns absolute inset-0 h-full w-full object-cover">
                    <div v-else class="ev-dark-section absolute inset-0">
                        <div class="ev-grid-bg absolute inset-0" />
                        <div class="absolute inset-0 grid place-items-center">
                            <span class="ev-spark grid h-28 w-28 place-items-center rounded-full bg-success/90 text-content-inverse">
                                <StorefrontIcon name="leaf" class="h-14 w-14" />
                            </span>
                        </div>
                    </div>
                    <div class="absolute inset-0 bg-gradient-to-t from-surface-inverse/80 via-surface-inverse/10 to-transparent" />
                    <p v-if="section.heading.eyebrow" class="absolute bottom-6 left-6 right-6 font-display text-2xl font-bold uppercase leading-tight text-content-inverse sm:text-3xl">
                        {{ section.heading.eyebrow }}
                        <span class="mt-3 block h-1 w-14 rounded-full bg-brand" aria-hidden="true" />
                    </p>
                </div>
            </div>
        </StorefrontContainer>

        <!-- Stats band -->
        <div v-if="stats.length" class="ev-dark-section relative text-content-inverse">
            <div class="ev-grid-bg pointer-events-none absolute inset-0 opacity-40" aria-hidden="true" />
            <StorefrontContainer class="relative">
                <dl class="grid grid-cols-2 gap-x-4 gap-y-6 py-8 sm:py-10 md:flex md:flex-wrap md:justify-around">
                    <div v-for="(stat, index) in stats" :key="`${stat.label}-${index}`" v-reveal="{ delay: index * 100 }" class="flex items-center gap-3 sm:gap-4">
                        <span class="grid h-12 w-12 shrink-0 place-items-center rounded-full border-2 border-brand/70 text-brand sm:h-14 sm:w-14">
                            <StorefrontIcon :name="statIcons[index % statIcons.length]" class="h-6 w-6" />
                        </span>
                        <div class="flex flex-col-reverse">
                            <dt class="text-xs text-content-inverse/70 sm:text-sm">{{ stat.label }}</dt>
                            <dd v-count-up class="font-display text-2xl font-bold tabular-nums text-content-inverse sm:text-3xl">{{ stat.value }}</dd>
                        </div>
                    </div>
                </dl>
            </StorefrontContainer>
        </div>
    </section>
</template>
