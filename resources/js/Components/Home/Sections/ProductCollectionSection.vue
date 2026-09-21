<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import ProductCard from '@/Components/Storefront/ProductCard.vue';
import StorefrontContainer from '@/Components/Storefront/StorefrontContainer.vue';

const props = defineProps({ section: { type: Object, required: true } });
const allowedVariants = ['marketplace_grid', 'compact_grid'];
const variant = computed(() => {
    if (allowedVariants.includes(props.section.variant)) return props.section.variant;
    console.warn(`[storefront] Unsupported featured_products variant: ${props.section.variant}`);
    return allowedVariants[0];
});
</script>

<template>
    <section v-if="section.items.length" class="storefront-section border-y border-line-subtle bg-gradient-to-b from-surface-card via-surface-page to-surface-page">
        <StorefrontContainer>
            <div class="mb-8 flex flex-col gap-5 border-b border-line pb-6 sm:flex-row sm:items-end sm:justify-between">
                <div class="max-w-2xl">
                    <p class="mb-3 font-display text-xs font-bold uppercase tracking-[0.2em] text-brand-text">Danh mục chọn lọc</p>
                    <h2 class="font-display text-3xl font-bold tracking-tight text-content-primary md:text-4xl">{{ section.heading.title }}</h2>
                    <p v-if="section.heading.subtitle" class="mt-3 text-base leading-relaxed text-content-secondary">{{ section.heading.subtitle }}</p>
                </div>
                <Link
                    :href="route('products.index')"
                    class="group inline-flex min-h-11 shrink-0 items-center gap-3 self-start rounded-full border border-line-strong bg-surface-card px-5 text-sm font-semibold text-content-primary shadow-ds-sm transition hover:-translate-y-0.5 hover:border-brand-border hover:bg-brand-soft sm:self-auto"
                >
                    Xem tất cả sản phẩm
                    <span aria-hidden="true" class="grid h-6 w-6 place-items-center rounded-full bg-brand text-base leading-none text-brand-contrast transition group-hover:translate-x-0.5">→</span>
                </Link>
            </div>

            <div class="grid gap-5 md:gap-6" :class="variant === 'compact_grid' ? 'md:grid-cols-2 xl:grid-cols-3' : 'sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4'">
                <ProductCard v-for="product in section.items" :key="product.id" :product="product" variant="featured" />
            </div>
        </StorefrontContainer>
    </section>
</template>
