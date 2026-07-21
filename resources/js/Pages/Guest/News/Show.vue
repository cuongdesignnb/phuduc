<script setup>
import GuestPageLayout from '@/Layouts/GuestPageLayout.vue';
import SeoHead from '@/Components/SeoHead.vue';
import { Link } from '@inertiajs/vue3';

defineProps({ post: Object, relatedPosts: Array, seo: Object, jsonLd: [Object, Array] });
</script>

<template>
    <SeoHead v-bind="seo" :json-ld="jsonLd" />
    <GuestPageLayout>
        <article class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 py-12">
            <!-- Header -->
            <div class="mb-8">
                <p class="text-sm mb-3">
                    <Link v-if="post.category" :href="route('news.index', { category: post.category.slug })" class="text-brand-hover font-semibold transition-colors">{{ $fixText(post.category.name) }}</Link>
                    <span v-else class="text-brand-hover font-semibold">Tin tức</span>
                    <span class="text-ink-light"> · </span>
                    <span class="text-ink-light">{{ new Date(post.created_at).toLocaleDateString('vi-VN') }}</span>
                </p>
                <h1 class="text-3xl md:text-4xl font-display font-bold text-ink-primary leading-tight mb-4">{{ $fixText(post.title) }}</h1>
                <p v-if="post.summary" class="text-lg text-ink-secondary">{{ $fixText(post.summary) }}</p>
            </div>

            <!-- Featured Image -->
            <div v-if="post.featured_image" class="mb-10 rounded-2xl overflow-hidden border border-surface-border">
                <img :src="'/storage/' + post.featured_image" :alt="$fixText(post.title)" class="w-full object-cover" />
            </div>

            <div class="storefront-divider mb-10"></div>

            <!-- Content -->
            <div class="prose prose-p:text-ink-secondary prose-headings:font-display prose-headings:text-ink-primary prose-a:text-brand-hover prose-strong:text-ink-primary prose-img:rounded-xl max-w-none" v-html="$fixText(post.content)"></div>

            <!-- Related Posts -->
            <div v-if="relatedPosts?.length" class="mt-14 border-t border-surface-border pt-8">
                <h2 class="text-xl font-display font-bold text-ink-primary mb-6">Bài viết liên quan</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <Link v-for="rp in relatedPosts" :key="rp.id" :href="route('news.show', rp.slug)" class="group flex gap-4 items-start storefront-card-interactive p-3">
                        <div v-if="rp.featured_image" class="w-24 h-16 rounded-lg overflow-hidden shrink-0 bg-surface-muted">
                            <img :src="'/storage/' + rp.featured_image" :alt="$fixText(rp.title)" class="w-full h-full object-cover" />
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-ink-primary group-hover:text-brand-hover transition-colors line-clamp-2">{{ $fixText(rp.title) }}</h3>
                            <p class="text-xs text-ink-light mt-1">{{ new Date(rp.created_at).toLocaleDateString('vi-VN') }}</p>
                        </div>
                    </Link>
                </div>
            </div>
        </article>
    </GuestPageLayout>
</template>

