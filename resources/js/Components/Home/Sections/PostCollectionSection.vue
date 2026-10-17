<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import HomeHeading from '../HomeHeading.vue';
import ResponsiveImage from '@/Components/Storefront/ResponsiveImage.vue';
import StorefrontContainer from '@/Components/Storefront/StorefrontContainer.vue';
import StorefrontIcon from '@/Components/Storefront/StorefrontIcon.vue';
import { vReveal } from '@/Composables/useMotion';

const props = defineProps({ section: { type: Object, required: true } });
const allowedVariants = ['editorial_grid', 'compact_grid'];
const variant = computed(() => {
    if (allowedVariants.includes(props.section.variant)) return props.section.variant;
    console.warn(`[storefront] Unsupported latest_posts variant: ${props.section.variant}`);
    return allowedVariants[0];
});
const formatDate = (value) => {
    if (!value) return '';
    const [year, month, day] = value.split('-');
    return day && month && year ? `${day}/${month}/${year}` : value;
};
</script>

<template>
    <section v-if="section.items.length" class="storefront-section bg-surface-card">
        <StorefrontContainer>
            <div class="mb-7 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <HomeHeading eyebrow="Tin tức & sự kiện" :title="section.heading.title" />
                <Link :href="route('news.index')" class="group inline-flex min-h-10 items-center gap-2 self-start rounded-full border border-brand-border px-4 text-sm font-semibold text-content-primary transition hover:bg-brand hover:text-brand-contrast sm:self-auto">
                    Xem tất cả tin tức
                    <StorefrontIcon name="arrow" class="h-4 w-4 transition group-hover:translate-x-0.5" />
                </Link>
            </div>
            <div class="grid gap-5" :class="variant === 'compact_grid' ? 'md:grid-cols-2' : 'md:grid-cols-2 xl:grid-cols-3'">
                <article
                    v-for="(post, index) in section.items"
                    :key="post.id"
                    v-reveal="{ delay: index * 90 }"
                    class="group flex overflow-hidden rounded-2xl border border-line bg-surface-card shadow-ds-sm transition duration-300 hover:-translate-y-1 hover:border-brand-border hover:shadow-card"
                    :class="variant === 'compact_grid' ? 'min-h-32' : 'flex-col'"
                >
                    <Link :href="route('news.show', post.slug)" class="block overflow-hidden" :class="variant === 'compact_grid' && 'w-2/5 max-w-48 shrink-0'">
                        <div class="h-full transition duration-500 group-hover:scale-105">
                            <ResponsiveImage v-if="post.image_url" :src="post.image_url" :alt="post.title" :aspect="variant === 'compact_grid' ? '1/1' : '16/9'" />
                            <div v-else class="ev-dark-section relative grid h-full place-items-center" :style="{ aspectRatio: variant === 'compact_grid' ? '1/1' : '16/9' }" role="img" :aria-label="post.title">
                                <div class="ev-grid-bg absolute inset-0" />
                                <span class="relative grid h-14 w-14 place-items-center rounded-2xl bg-brand text-brand-contrast"><StorefrontIcon name="news" class="h-7 w-7" /></span>
                            </div>
                        </div>
                    </Link>
                    <div class="flex flex-1 flex-col p-5">
                        <p class="flex flex-wrap items-center gap-2 text-xs font-semibold text-content-muted">
                            <span v-if="post.category" class="rounded-full bg-brand-soft px-2.5 py-1 font-bold uppercase tracking-wide text-brand-text">{{ post.category.name || post.category }}</span>
                            <time v-if="post.published_at" :datetime="post.published_at">{{ formatDate(post.published_at) }}</time>
                        </p>
                        <h3 class="mt-3 font-display text-lg font-bold leading-snug text-content-primary">
                            <Link :href="route('news.show', post.slug)" class="line-clamp-2 transition hover:text-brand-text">{{ post.title }}</Link>
                        </h3>
                        <p v-if="post.summary && variant !== 'compact_grid'" class="mt-2 line-clamp-2 text-sm leading-6 text-content-secondary">{{ post.summary }}</p>
                    </div>
                </article>
            </div>
        </StorefrontContainer>
    </section>
</template>
