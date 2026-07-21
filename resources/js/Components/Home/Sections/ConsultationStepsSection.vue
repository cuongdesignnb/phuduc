<script setup>
import SectionHeader from '@/Components/Storefront/SectionHeader.vue';
import StorefrontContainer from '@/Components/Storefront/StorefrontContainer.vue';

defineProps({ section: { type: Object, required: true } });
const toneClass = (tone) => ({
    yellow: 'border-brand bg-brand-soft',
    blue: 'border-info bg-info-soft',
    green: 'border-success bg-success-soft',
}[tone] || 'border-line-strong bg-surface-muted');
</script>

<template>
    <section v-if="section.items.length" class="storefront-section bg-surface-page">
        <StorefrontContainer size="content">
            <SectionHeader :title="section.heading.title" align="center" />
            <ol class="grid gap-5 md:grid-cols-3">
                <li v-for="(item, index) in section.items" :key="item.id" class="storefront-card relative overflow-hidden border-t-4 p-6" :class="toneClass(item.metadata?.tone)">
                    <span class="font-display text-4xl font-bold text-content-primary">{{ item.title || String(index + 1).padStart(2, '0') }}</span>
                    <h3 v-if="item.subtitle" class="mt-4 font-display text-xl font-bold text-content-primary">{{ item.subtitle }}</h3>
                    <p v-if="item.description" class="mt-2 text-sm leading-6 text-content-secondary">{{ item.description }}</p>
                </li>
            </ol>
        </StorefrontContainer>
    </section>
</template>
