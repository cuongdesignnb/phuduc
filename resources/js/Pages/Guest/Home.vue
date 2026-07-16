<script setup>
import HomeSectionRenderer from '@/Components/Home/HomeSectionRenderer.vue';
import SeoHead from '@/Components/SeoHead.vue';
import GuestPageLayout from '@/Layouts/GuestPageLayout.vue';

defineProps({
    site: { type: Object, required: true },
    navigation: { type: Object, required: true },
    page: { type: Object, required: true },
});
</script>

<template>
    <SeoHead v-bind="page.seo" :json-ld="page.json_ld" />

    <GuestPageLayout>
        <HomeSectionRenderer
            v-for="section in page.sections"
            :key="section.key"
            :section="section"
            :site="site"
        />
    </GuestPageLayout>
</template>
