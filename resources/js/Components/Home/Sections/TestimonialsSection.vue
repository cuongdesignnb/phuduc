<script setup>
import ResponsiveImage from '@/Components/Storefront/ResponsiveImage.vue';
import SectionHeader from '@/Components/Storefront/SectionHeader.vue';
import StorefrontContainer from '@/Components/Storefront/StorefrontContainer.vue';

defineProps({ section: { type: Object, required: true } });
</script>

<template>
    <section v-if="section.items.length" class="storefront-section bg-surface-page">
        <StorefrontContainer>
            <SectionHeader :title="section.heading.title" />
            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                <blockquote v-for="item in section.items" :key="item.id" class="storefront-card flex h-full flex-col p-6 sm:p-7">
                    <svg class="h-8 w-8 text-brand" aria-hidden="true" viewBox="0 0 32 32" fill="currentColor"><path d="M13 7v8H8c0 4 2 6 5 7v3C7 24 4 20 4 14V7h9Zm15 0v8h-5c0 4 2 6 5 7v3c-6-1-9-5-9-11V7h9Z" /></svg>
                    <p class="mt-4 flex-1 text-base leading-7 text-content-secondary">{{ item.description }}</p>
                    <footer class="mt-6 flex items-center gap-3 border-t border-line-subtle pt-5">
                        <div v-if="item.image_url" class="h-12 w-12 shrink-0 overflow-hidden rounded-full">
                            <ResponsiveImage :src="item.image_url" :alt="item.title" aspect="1/1" />
                        </div>
                        <span v-else class="grid h-12 w-12 shrink-0 place-items-center rounded-full bg-brand-soft font-display font-bold text-content-primary">{{ item.metadata?.avatar_text || item.title?.charAt(0) }}</span>
                        <span><strong class="block text-content-primary">{{ item.title }}</strong><small v-if="item.subtitle" class="text-content-muted">{{ item.subtitle }}</small></span>
                    </footer>
                </blockquote>
            </div>
        </StorefrontContainer>
    </section>
</template>
