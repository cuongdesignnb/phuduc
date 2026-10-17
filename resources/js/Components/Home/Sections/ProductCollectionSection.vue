<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import HomeHeading from '../HomeHeading.vue';
import TrackArrows from '../TrackArrows.vue';
import ResponsiveImage from '@/Components/Storefront/ResponsiveImage.vue';
import StorefrontContainer from '@/Components/Storefront/StorefrontContainer.vue';
import StorefrontIcon from '@/Components/Storefront/StorefrontIcon.vue';
import { scrollTrack, vReveal } from '@/Composables/useMotion';

const props = defineProps({ section: { type: Object, required: true }, site: { type: Object, default: () => ({}) } });
const allowedVariants = ['marketplace_grid', 'compact_grid'];
const variant = computed(() => {
    if (allowedVariants.includes(props.section.variant)) return props.section.variant;
    console.warn(`[storefront] Unsupported featured_products variant: ${props.section.variant}`);
    return allowedVariants[0];
});
const page = usePage();
const phone = computed(() => props.site.hotline || props.site.phone || page.props.site?.hotline || page.props.site?.phone || '');
const track = ref(null);
const perRow = computed(() => (variant.value === 'compact_grid' ? 3 : 4));
const specIcon = (key) => ({ payload: 'forklift', range: 'gauge', battery: 'battery' }[key] || 'bolt');
</script>

<template>
    <section v-if="section.items.length" class="ev-dark-section storefront-section relative isolate overflow-hidden text-content-inverse">
        <div class="ev-grid-bg pointer-events-none absolute inset-0 -z-10 opacity-50" aria-hidden="true" />
        <StorefrontContainer>
            <div class="mb-8 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                <HomeHeading eyebrow="Sản phẩm nổi bật" :title="section.heading.title" :description="section.heading.subtitle" inverse />
                <div class="flex items-center gap-3">
                    <Link
                        :href="route('products.index')"
                        class="group inline-flex min-h-10 items-center gap-2 rounded-full border border-brand px-4 text-sm font-semibold text-content-inverse transition hover:bg-brand hover:text-brand-contrast"
                    >
                        Xem tất cả sản phẩm
                        <StorefrontIcon name="arrow" class="h-4 w-4 transition group-hover:translate-x-0.5" />
                    </Link>
                    <TrackArrows v-if="section.items.length > perRow" inverse class="hidden sm:flex" @prev="scrollTrack(track, -1)" @next="scrollTrack(track, 1)" />
                </div>
            </div>

            <div ref="track" class="no-scrollbar -mx-[var(--ds-page-gutter)] flex snap-x snap-mandatory gap-4 overflow-x-auto scroll-px-[var(--ds-page-gutter)] px-[var(--ds-page-gutter)] pb-4 pt-1 sm:gap-5">
                <article
                    v-for="(product, index) in section.items"
                    :key="product.id"
                    v-reveal="{ delay: index * 90 }"
                    data-track-item
                    class="group flex shrink-0 snap-start flex-col overflow-hidden rounded-2xl bg-surface-card text-content-primary shadow-card transition duration-300 hover:-translate-y-1.5 hover:shadow-card-hover"
                    :class="variant === 'compact_grid'
                        ? 'w-[84%] sm:w-[46%] lg:w-[calc((100%-2.5rem)/3)]'
                        : 'w-[80%] sm:w-[46%] lg:w-[calc((100%-3.75rem)/4)]'"
                >
                    <Link :href="route('products.show', product.slug)" class="relative block overflow-hidden bg-gradient-to-b from-surface-muted to-surface-card">
                        <div class="transition duration-500 group-hover:scale-105">
                            <ResponsiveImage :src="product.image_url" :alt="product.name" aspect="4/3" class="!bg-transparent" />
                        </div>
                        <span v-if="product.review_count" class="absolute right-3 top-3 rounded-full bg-brand px-2.5 py-1 text-[0.6875rem] font-bold text-brand-contrast shadow-ds-sm">
                            ★ {{ product.average_rating }}
                        </span>
                        <span class="absolute inset-x-6 bottom-2 h-3 rounded-full bg-surface-inverse/10 blur-md" aria-hidden="true" />
                    </Link>

                    <div class="flex flex-1 flex-col px-5 pb-5 pt-3">
                        <h3 class="font-display text-lg font-bold leading-snug">
                            <Link :href="route('products.show', product.slug)" class="line-clamp-2 transition hover:text-brand-text">{{ product.name }}</Link>
                        </h3>
                        <p v-if="product.sku" class="mt-1 text-xs font-semibold uppercase tracking-wider text-content-muted">{{ product.sku }}</p>

                        <ul v-if="product.card_specifications?.length" class="mt-4 space-y-2 text-sm text-content-secondary">
                            <li v-for="specification in product.card_specifications.slice(0, 3)" :key="`${specification.key}-${specification.value}`" class="flex items-start gap-2">
                                <span class="mt-0.5 grid h-5 w-5 shrink-0 place-items-center rounded-md bg-brand-soft text-brand-text" aria-hidden="true">
                                    <StorefrontIcon :name="specIcon(specification.key)" class="h-3.5 w-3.5" />
                                </span>
                                <span>{{ specification.label || specification.key }}: <strong class="font-semibold text-content-primary">{{ specification.value }}</strong></span>
                            </li>
                        </ul>
                        <p v-else class="mt-4 text-sm leading-6 text-content-secondary">Liên hệ để nhận tư vấn cấu hình phù hợp.</p>

                        <p class="mt-4 font-display text-lg font-bold text-content-primary">{{ product.price_display }}</p>

                        <div class="mt-auto flex items-center justify-between gap-3 pt-4">
                            <a
                                v-if="phone"
                                :href="`tel:${phone}`"
                                class="ev-shine inline-flex min-h-10 items-center gap-2 rounded-full bg-brand px-4 text-sm font-bold text-brand-contrast transition hover:bg-brand-hover"
                            >
                                Liên hệ báo giá
                                <StorefrontIcon name="arrow" class="h-4 w-4" />
                            </a>
                            <Link :href="route('products.show', product.slug)" class="inline-flex min-h-10 items-center gap-1 text-sm font-semibold text-content-secondary transition hover:text-brand-text" :class="!phone && 'rounded-full bg-brand px-4 font-bold text-brand-contrast hover:text-brand-contrast'">
                                Xem chi tiết
                                <StorefrontIcon name="chevronRight" class="h-4 w-4" />
                            </Link>
                        </div>
                    </div>
                </article>
            </div>
        </StorefrontContainer>
    </section>
</template>
