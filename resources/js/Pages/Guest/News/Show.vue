<script setup>
import { Link } from '@inertiajs/vue3';
import GuestPageLayout from '@/Layouts/GuestPageLayout.vue';
import SeoHead from '@/Components/SeoHead.vue';
import Breadcrumbs from '@/Components/Storefront/Breadcrumbs.vue';
import NewsCard from '@/Components/Storefront/NewsCard.vue';
import PageHero from '@/Components/Storefront/PageHero.vue';
import ResponsiveImage from '@/Components/Storefront/ResponsiveImage.vue';
import RichContent from '@/Components/Storefront/RichContent.vue';
import SectionHeader from '@/Components/Storefront/SectionHeader.vue';
import StorefrontContainer from '@/Components/Storefront/StorefrontContainer.vue';

defineProps({ page: { type: Object, required: true } });
</script>

<template>
    <SeoHead v-bind="page.seo" :json-ld="page.json_ld" />
    <GuestPageLayout>
        <PageHero v-bind="page.hero">
            <Breadcrumbs :items="page.breadcrumbs" class="mt-6" />
        </PageHero>

        <StorefrontContainer size="content" class="py-10">
            <article>
                <header class="mb-6">
                    <p class="text-sm text-content-muted">
                        <Link v-if="page.post.category" :href="route('news.index', { category: page.post.category.slug })" class="font-semibold text-brand-text">{{ page.post.category.name }}</Link>
                        <span v-if="page.post.category" aria-hidden="true"> / </span>
                        <time v-if="page.post.published_at" :datetime="page.post.published_at">{{ page.post.published_at_display }}</time>
                    </p>
                </header>

                <ResponsiveImage v-if="page.post.image_url" :src="page.post.image_url" :alt="page.post.title" aspect="16/9" loading="eager" class="mb-8 rounded-lg" />
                <RichContent :html="page.post.content_html" />
            </article>

            <section v-if="page.related_posts.length" class="mt-12">
                <SectionHeader title="Bài viết liên quan" />
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <NewsCard v-for="post in page.related_posts" :key="post.id" :post="post" variant="compact" />
                </div>
            </section>
        </StorefrontContainer>
    </GuestPageLayout>
</template>
