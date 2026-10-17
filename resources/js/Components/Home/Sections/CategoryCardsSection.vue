<script setup>
import { Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import HomeHeading from '../HomeHeading.vue';
import TrackArrows from '../TrackArrows.vue';
import StorefrontContainer from '@/Components/Storefront/StorefrontContainer.vue';
import StorefrontIcon from '@/Components/Storefront/StorefrontIcon.vue';
import { scrollTrack, vReveal } from '@/Composables/useMotion';

const props = defineProps({ section: { type: Object, required: true } });
const allowedVariants = ['cards', 'compact_cards'];
const variant = computed(() => {
    if (allowedVariants.includes(props.section.variant)) return props.section.variant;
    console.warn(`[storefront] Unsupported category_cards variant: ${props.section.variant}`);
    return allowedVariants[0];
});
const track = ref(null);
const perRow = computed(() => (variant.value === 'compact_cards' ? 6 : 4));
const toneClass = (tone) => ({
    cart: 'bg-brand-soft text-brand-text',
    crane: 'bg-info-soft text-info',
    forklift: 'bg-success-soft text-success',
}[tone] || 'bg-surface-muted text-content-secondary');
</script>

<template>
    <section v-if="section.items.length" class="storefront-section bg-surface-page">
        <StorefrontContainer>
            <div class="mb-7 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <HomeHeading eyebrow="Danh mục sản phẩm" :title="section.heading.title" />
                <div class="flex items-end gap-6">
                    <p v-if="section.heading.subtitle" class="hidden max-w-xs text-sm leading-6 text-content-secondary lg:block">{{ section.heading.subtitle }}</p>
                    <TrackArrows v-if="section.items.length > perRow" class="hidden sm:flex" @prev="scrollTrack(track, -1)" @next="scrollTrack(track, 1)" />
                </div>
            </div>

            <div
                ref="track"
                class="no-scrollbar -mx-[var(--ds-page-gutter)] flex snap-x snap-mandatory gap-4 overflow-x-auto scroll-px-[var(--ds-page-gutter)] px-[var(--ds-page-gutter)] pb-2 sm:gap-5"
            >
                <component
                    :is="item.url ? Link : 'article'"
                    v-for="(item, index) in section.items"
                    :key="item.id"
                    v-reveal="{ delay: index * 80 }"
                    data-track-item
                    :href="item.url || undefined"
                    class="group relative flex shrink-0 snap-start flex-col overflow-hidden rounded-2xl border border-line bg-surface-card p-5 shadow-card transition duration-300 hover:-translate-y-1 hover:border-brand-border hover:shadow-card-hover"
                    :class="variant === 'compact_cards'
                        ? 'min-h-40 w-[44%] sm:w-[30%] lg:w-[calc((100%-6.25rem)/6)]'
                        : 'min-h-52 w-[78%] sm:w-[46%] lg:w-[calc((100%-3.75rem)/4)]'"
                >
                    <span class="relative z-10 max-w-[70%]">
                        <strong class="block font-display text-lg font-bold leading-tight text-content-primary">{{ item.title }}</strong>
                        <span v-if="item.subtitle && variant === 'cards'" class="mt-1.5 block text-sm leading-5 text-content-muted">{{ item.subtitle }}</span>
                    </span>

                    <img
                        v-if="item.image_url"
                        :src="item.image_url"
                        :alt="item.title"
                        loading="lazy"
                        decoding="async"
                        class="pointer-events-none absolute bottom-3 right-2 h-[62%] w-[66%] object-contain object-right-bottom transition duration-500 group-hover:-translate-x-1 group-hover:scale-105"
                    >
                    <span v-else class="absolute bottom-5 right-5 grid h-20 w-20 place-items-center rounded-2xl transition duration-500 group-hover:rotate-6 group-hover:scale-105" :class="toneClass(item.metadata?.tone)">
                        <StorefrontIcon :name="item.icon" class="h-10 w-10" />
                    </span>

                    <span v-if="item.url" class="relative z-10 mt-auto grid h-8 w-8 place-items-center rounded-full border border-brand-border text-brand-text transition group-hover:bg-brand group-hover:text-brand-contrast" aria-hidden="true">
                        <StorefrontIcon name="chevronRight" class="h-4 w-4" />
                    </span>
                </component>
            </div>
        </StorefrontContainer>
    </section>
</template>
