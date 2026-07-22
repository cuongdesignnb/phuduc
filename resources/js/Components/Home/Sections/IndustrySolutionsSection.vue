<script setup>
import { Link } from '@inertiajs/vue3';
import ResponsiveImage from '@/Components/Storefront/ResponsiveImage.vue';
import SectionHeader from '@/Components/Storefront/SectionHeader.vue';
import StorefrontContainer from '@/Components/Storefront/StorefrontContainer.vue';

defineProps({ section: { type: Object, required: true } });
const toneClass = (tone) => ({
    warehouse: 'border-brand-border bg-brand-soft',
    factory: 'border-info/25 bg-info-soft',
    green: 'border-success/25 bg-success-soft',
}[tone] || 'border-line bg-surface-card');
</script>

<template>
    <section v-if="section.items.length" class="storefront-section bg-surface-muted">
        <StorefrontContainer>
            <SectionHeader :title="section.heading.title" />
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
                <component
                    :is="item.url ? Link : 'article'"
                    v-for="item in section.items"
                    :key="item.id"
                    :href="item.url || undefined"
                    class="group overflow-hidden rounded-xl border transition hover:-translate-y-0.5"
                    :class="toneClass(item.metadata?.tone)"
                >
                    <ResponsiveImage :src="item.image_url" :alt="item.title" aspect="4/3" />
                    <strong class="block p-4 font-display text-lg font-bold text-content-primary">{{ item.title }}</strong>
                </component>
            </div>
        </StorefrontContainer>
    </section>
</template>
