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
                    <Link v-if="post.category" :href="route('news.index', { category: post.category.slug })" class="text-volt-500 hover:text-volt-400 transition-colors">{{ post.category.name }}</Link>
                    <span v-else class="text-volt-500">Tin tức</span>
                    <span class="text-carbon-600"> · </span>
                    <span class="text-carbon-500">{{ new Date(post.created_at).toLocaleDateString('vi-VN') }}</span>
                </p>
                <h1 class="text-3xl md:text-4xl font-display font-bold text-white leading-tight mb-4">{{ post.title }}</h1>
                <p v-if="post.summary" class="text-lg text-carbon-400">{{ post.summary }}</p>
            </div>

            <!-- Featured Image -->
            <div v-if="post.featured_image" class="mb-10 rounded-2xl overflow-hidden border border-white/[0.06]">
                <img :src="'/storage/' + post.featured_image" :alt="post.title" class="w-full object-cover" />
            </div>

            <div class="neon-line mb-10"></div>

            <!-- Content -->
            <div class="prose prose-invert prose-p:text-carbon-300 prose-headings:font-display prose-a:text-volt-400 prose-strong:text-white prose-img:rounded-xl max-w-none" v-html="post.content"></div>

            <!-- Related Posts -->
            <div v-if="relatedPosts?.length" class="mt-14 border-t border-white/[0.06] pt-8">
                <h2 class="text-xl font-display font-bold text-white mb-6">Bài viết liên quan</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <Link v-for="rp in relatedPosts" :key="rp.id" :href="route('news.show', rp.slug)" class="group flex gap-4 items-start glass-card p-3">
                        <div v-if="rp.featured_image" class="w-24 h-16 rounded-lg overflow-hidden shrink-0 bg-carbon-800">
                            <img :src="'/storage/' + rp.featured_image" :alt="rp.title" class="w-full h-full object-cover" />
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-white group-hover:text-volt-400 transition-colors line-clamp-2">{{ rp.title }}</h3>
                            <p class="text-xs text-carbon-500 mt-1">{{ new Date(rp.created_at).toLocaleDateString('vi-VN') }}</p>
                        </div>
                    </Link>
                </div>
            </div>
        </article>
    </GuestPageLayout>
</template>
