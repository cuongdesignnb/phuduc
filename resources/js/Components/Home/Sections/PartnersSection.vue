<script setup>
import { Link } from '@inertiajs/vue3';
import SectionHeader from '@/Components/Storefront/SectionHeader.vue';
import StorefrontContainer from '@/Components/Storefront/StorefrontContainer.vue';

defineProps({ section: { type: Object, required: true } });
const isExternal = (url) => /^(https?:)?\/\//.test(url || '');
const componentFor = (url) => !url ? 'div' : isExternal(url) ? 'a' : Link;
</script>

<template>
    <section v-if="section.items.length" class="storefront-section-compact border-y border-line-subtle bg-surface-muted">
        <StorefrontContainer>
            <SectionHeader :title="section.heading.title" />
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
                <component
                    :is="componentFor(item.url)"
                    v-for="item in section.items"
                    :key="item.id"
                    :href="item.url || undefined"
                    :target="isExternal(item.url) ? '_blank' : undefined"
                    :rel="isExternal(item.url) ? 'noopener noreferrer' : undefined"
                    class="grid min-h-24 place-items-center rounded-xl border border-line bg-surface-card p-4 text-center font-display font-bold text-content-secondary transition hover:border-brand-border"
                >
                    <img v-if="item.image_url" :src="item.image_url" :alt="item.title" loading="lazy" width="180" height="72" class="max-h-14 max-w-full object-contain grayscale transition hover:grayscale-0">
                    <span v-else>{{ item.title }}</span>
                </component>
            </div>
        </StorefrontContainer>
    </section>
</template>
