<script setup>
import { Link } from '@inertiajs/vue3';
import ResponsiveImage from './ResponsiveImage.vue';

defineProps({
    post: { type: Object, required: true },
    variant: { type: String, default: 'editorial', validator: (value) => ['editorial', 'compact'].includes(value) },
});
</script>

<template>
    <article class="group storefront-card-interactive overflow-hidden" :class="variant === 'compact' && 'flex min-h-32'">
        <Link :href="route('news.show', post.slug)" :class="variant === 'compact' ? 'w-2/5 max-w-48' : 'block'">
            <ResponsiveImage :src="post.image_url" :alt="post.title" :aspect="variant === 'compact' ? '1/1' : '16/9'" />
        </Link>
        <div class="flex flex-1 flex-col p-5">
            <p v-if="post.category" class="text-xs font-bold uppercase tracking-wide text-brand-text">{{ post.category.name || post.category }}</p>
            <h3 class="mt-1 font-display text-lg font-bold leading-snug text-content-primary">
                <Link :href="route('news.show', post.slug)" class="transition hover:text-brand-text">{{ post.title }}</Link>
            </h3>
            <p v-if="post.summary && variant !== 'compact'" class="mt-2 line-clamp-3 text-sm leading-6 text-content-secondary">{{ post.summary }}</p>
            <time v-if="post.published_at" :datetime="post.published_at" class="mt-auto pt-3 text-xs text-content-muted">{{ post.published_at_display }}</time>
        </div>
    </article>
</template>
