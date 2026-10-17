<script setup>
import { computed } from 'vue';
import HomeSectionRenderer from '@/Components/Home/HomeSectionRenderer.vue';
import SeoHead from '@/Components/SeoHead.vue';
import GuestPageLayout from '@/Layouts/GuestPageLayout.vue';

const props = defineProps({
    site: { type: Object, required: true },
    navigation: { type: Object, required: true },
    page: { type: Object, required: true },
});

const findSection = (key) => props.page.sections.find((section) => section.key === key);
// Benefit strip items render as hero badges; it only renders on its own without a hero.
const hasHero = computed(() => !!findSection('hero'));
const benefitItems = computed(() => findSection('benefit_strip')?.items || []);
// Featured product photos fill visual slots whose own image has not been configured yet.
const showcase = computed(() => (findSection('featured_products')?.items || []).filter((product) => product.image_url));
const sections = computed(() => props.page.sections.filter((section) => !(hasHero.value && section.key === 'benefit_strip')));
const extraFor = (section) => ({
    hero: { highlights: benefitItems.value, showcase: showcase.value },
    energy_banner: { showcase: showcase.value },
}[section.key] || {});
</script>

<template>
    <SeoHead v-bind="page.seo" :json-ld="page.json_ld" />

    <GuestPageLayout>
        <HomeSectionRenderer
            v-for="section in sections"
            :key="section.key"
            :section="section"
            :site="site"
            :extra="extraFor(section)"
        />
    </GuestPageLayout>
</template>
