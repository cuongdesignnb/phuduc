<script setup>
import { computed } from 'vue';
import NewsCard from '@/Components/Storefront/NewsCard.vue';
import SectionHeader from '@/Components/Storefront/SectionHeader.vue';
import StorefrontContainer from '@/Components/Storefront/StorefrontContainer.vue';

const props = defineProps({ section: { type: Object, required: true } });
const allowedVariants = ['editorial_grid', 'compact_grid'];
const variant = computed(() => {
    if (allowedVariants.includes(props.section.variant)) return props.section.variant;
    console.warn(`[storefront] Unsupported latest_posts variant: ${props.section.variant}`);
    return allowedVariants[0];
});
</script>

<template>
    <section v-if="section.items.length" class="storefront-section bg-surface-page">
        <StorefrontContainer>
            <SectionHeader :title="section.heading.title" action-label="Xem tất cả tin tức" :action-href="route('news.index')" />
            <div class="grid gap-5" :class="variant === 'compact_grid' ? 'md:grid-cols-2' : 'md:grid-cols-2 xl:grid-cols-3'">
                <NewsCard v-for="post in section.items" :key="post.id" :post="post" :variant="variant === 'compact_grid' ? 'compact' : 'editorial'" />
            </div>
        </StorefrontContainer>
    </section>
</template>
