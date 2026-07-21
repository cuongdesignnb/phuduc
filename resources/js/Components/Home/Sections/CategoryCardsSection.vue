<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import ResponsiveImage from '@/Components/Storefront/ResponsiveImage.vue';
import SectionHeader from '@/Components/Storefront/SectionHeader.vue';
import StorefrontContainer from '@/Components/Storefront/StorefrontContainer.vue';
import StorefrontIcon from '@/Components/Storefront/StorefrontIcon.vue';

const props = defineProps({ section: { type: Object, required: true } });
const allowedVariants = ['cards', 'compact_cards'];
const variant = computed(() => {
    if (allowedVariants.includes(props.section.variant)) return props.section.variant;
    console.warn(`[storefront] Unsupported category_cards variant: ${props.section.variant}`);
    return allowedVariants[0];
});
const toneClass = (tone) => ({
    cart: 'bg-brand-soft text-content-primary',
    crane: 'bg-info-soft text-info',
    forklift: 'bg-success-soft text-success',
}[tone] || 'bg-surface-muted text-content-secondary');
</script>

<template>
    <section v-if="section.items.length" class="storefront-section-compact">
        <StorefrontContainer>
            <SectionHeader :title="section.heading.title" :description="section.heading.subtitle" />
            <div class="grid gap-4" :class="variant === 'compact_cards' ? 'grid-cols-2 md:grid-cols-3 xl:grid-cols-6' : 'sm:grid-cols-2 lg:grid-cols-3'">
                <component
                    :is="item.url ? Link : 'article'"
                    v-for="item in section.items"
                    :key="item.id"
                    :href="item.url || undefined"
                    class="group storefront-card-interactive flex items-center gap-4 overflow-hidden"
                    :class="variant === 'compact_cards' ? 'min-h-24 p-3 sm:p-4' : 'min-h-36 p-5'"
                >
                    <div v-if="item.image_url" class="shrink-0 overflow-hidden rounded-lg" :class="variant === 'compact_cards' ? 'h-14 w-14' : 'h-24 w-28'">
                        <ResponsiveImage :src="item.image_url" :alt="item.title" aspect="1/1" object-fit="contain" />
                    </div>
                    <span v-else class="grid shrink-0 place-items-center rounded-lg" :class="[toneClass(item.metadata?.tone), variant === 'compact_cards' ? 'h-11 w-11' : 'h-16 w-16']">
                        <StorefrontIcon :name="item.icon" class="h-6 w-6" />
                    </span>
                    <span class="min-w-0">
                        <strong class="block font-display font-bold leading-tight text-content-primary" :class="variant === 'compact_cards' ? 'text-sm sm:text-base' : 'text-lg'">{{ item.title }}</strong>
                        <span v-if="item.subtitle && variant === 'cards'" class="mt-1 block text-sm text-content-muted">{{ item.subtitle }} <span v-if="item.url" aria-hidden="true">→</span></span>
                    </span>
                </component>
            </div>
        </StorefrontContainer>
    </section>
</template>
