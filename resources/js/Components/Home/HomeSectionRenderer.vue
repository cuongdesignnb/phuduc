<script setup>
import { computed } from 'vue';
import BenefitStripSection from './Sections/BenefitStripSection.vue';
import CategoryCardsSection from './Sections/CategoryCardsSection.vue';
import ConsultationStepsSection from './Sections/ConsultationStepsSection.vue';
import EnergyBannerSection from './Sections/EnergyBannerSection.vue';
import HeroSection from './Sections/HeroSection.vue';
import IndustrySolutionsSection from './Sections/IndustrySolutionsSection.vue';
import PartnersSection from './Sections/PartnersSection.vue';
import PostCollectionSection from './Sections/PostCollectionSection.vue';
import ProductCollectionSection from './Sections/ProductCollectionSection.vue';
import TestimonialsSection from './Sections/TestimonialsSection.vue';

const props = defineProps({
    section: { type: Object, required: true },
    site: { type: Object, required: true },
    // Extra props for a specific section (e.g. hero highlights).
    extra: { type: Object, default: () => ({}) },
});

const components = {
    hero: HeroSection,
    category_cards: CategoryCardsSection,
    benefit_strip: BenefitStripSection,
    featured_products: ProductCollectionSection,
    energy_banner: EnergyBannerSection,
    industry_solutions: IndustrySolutionsSection,
    testimonials: TestimonialsSection,
    partners: PartnersSection,
    latest_posts: PostCollectionSection,
    consultation_steps: ConsultationStepsSection,
};
const needsSite = ['hero', 'featured_products'];
const bindings = computed(() => ({
    section: props.section,
    ...(needsSite.includes(props.section.key) ? { site: props.site } : {}),
    ...props.extra,
}));
</script>

<template>
    <component
        :is="components[props.section.key]"
        v-if="components[props.section.key]"
        v-bind="bindings"
    />
</template>
