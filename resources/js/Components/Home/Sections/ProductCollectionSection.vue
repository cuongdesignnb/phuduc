<script setup>
import { computed } from 'vue';
import ProductCard from '@/Components/Storefront/ProductCard.vue';
import SectionHeader from '@/Components/Storefront/SectionHeader.vue';
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
    <section v-if="section.items.length" class="storefront-section bg-surface-page">
        <StorefrontContainer>
            <SectionHeader :title="section.heading.title" :description="section.heading.subtitle" action-label="Xem tất cả sản phẩm" :action-href="route('products.index')" />
            <div class="grid gap-5" :class="variant === 'compact_grid' ? 'md:grid-cols-2 xl:grid-cols-3' : 'sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4'">
                <ProductCard v-for="product in section.items" :key="product.id" :product="product" :variant="variant === 'compact_grid' ? 'compact' : 'marketplace'" />
            </div>
        </StorefrontContainer>
    </section>
</template>
