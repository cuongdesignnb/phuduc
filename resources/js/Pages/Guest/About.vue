<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import GuestPageLayout from '@/Layouts/GuestPageLayout.vue';
import SeoHead from '@/Components/SeoHead.vue';
import Breadcrumbs from '@/Components/Storefront/Breadcrumbs.vue';
import PageHero from '@/Components/Storefront/PageHero.vue';
import RichContent from '@/Components/Storefront/RichContent.vue';
import SectionHeader from '@/Components/Storefront/SectionHeader.vue';
import StorefrontContainer from '@/Components/Storefront/StorefrontContainer.vue';
import UiButton from '@/Components/Storefront/UiButton.vue';
import UiCard from '@/Components/Storefront/UiCard.vue';

defineProps({ page: { type: Object, required: true } });

const inertiaPage = usePage();
const site = computed(() => inertiaPage.props.site || {});
const phone = computed(() => site.value.hotline || site.value.phone || '');
const hasContact = computed(() => phone.value || site.value.email || site.value.address || site.value.working_hours);
</script>

<template>
    <SeoHead v-bind="page.seo" :json-ld="page.json_ld" />
    <GuestPageLayout>
        <PageHero v-bind="page.hero">
            <Breadcrumbs :items="page.breadcrumbs" class="mt-6" />
        </PageHero>

        <StorefrontContainer size="content" class="py-12">
            <section v-if="page.about.content_html" class="storefront-card p-6">
                <RichContent :html="page.about.content_html" />
            </section>

            <section v-if="page.about.mission || page.about.vision" class="mt-10 grid gap-6 md:grid-cols-2">
                <UiCard v-if="page.about.mission" class="p-6">
                    <h2 class="font-display text-2xl font-bold text-content-primary">Sứ mệnh</h2>
                    <p class="mt-3 text-content-secondary">{{ page.about.mission }}</p>
                </UiCard>
                <UiCard v-if="page.about.vision" class="p-6">
                    <h2 class="font-display text-2xl font-bold text-content-primary">Tầm nhìn</h2>
                    <p class="mt-3 text-content-secondary">{{ page.about.vision }}</p>
                </UiCard>
            </section>

            <section v-if="hasContact" class="mt-10">
                <SectionHeader title="Liên hệ" />
                <div class="storefront-card space-y-3 p-6 text-content-secondary">
                    <p v-if="site.address">{{ site.address }}</p>
                    <p v-if="site.working_hours">{{ site.working_hours }}</p>
                    <div class="flex flex-wrap gap-3 pt-2">
                        <UiButton v-if="phone" :href="`tel:${phone}`">Gọi điện</UiButton>
                        <UiButton v-if="site.email" :href="`mailto:${site.email}`" variant="outline">Email</UiButton>
                    </div>
                </div>
            </section>
        </StorefrontContainer>
    </GuestPageLayout>
</template>
