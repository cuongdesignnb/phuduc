<script setup>
import { computed } from 'vue';
import ResponsiveImage from '@/Components/Storefront/ResponsiveImage.vue';
import StorefrontContainer from '@/Components/Storefront/StorefrontContainer.vue';
import UiButton from '@/Components/Storefront/UiButton.vue';

const props = defineProps({ section: { type: Object, required: true }, site: { type: Object, default: () => ({}) } });
const allowedVariants = ['industrial_marketplace', 'split'];
const variant = computed(() => {
    if (allowedVariants.includes(props.section.variant)) return props.section.variant;
    console.warn(`[storefront] Unsupported hero variant: ${props.section.variant}`);
    return allowedVariants[0];
});
const primaryCta = computed(() => {
    const cta = props.section.config?.primary_cta;
    return cta?.label && cta?.url ? cta : null;
});
const secondaryCta = computed(() => {
    const cta = props.section.config?.secondary_cta;
    if (!cta?.label) return null;
    if (cta.action === 'phone') {
        const phone = props.site.hotline || props.site.phone;
        return phone ? { ...cta, url: `tel:${phone}` } : null;
    }
    return cta.url ? cta : null;
});
</script>

<template>
    <section class="relative overflow-hidden border-b border-line-subtle" :class="variant === 'industrial_marketplace' ? 'bg-surface-muted' : 'bg-surface-page'">
        <div v-if="variant === 'industrial_marketplace'" class="pointer-events-none absolute inset-0 opacity-40" aria-hidden="true" style="background-image: linear-gradient(rgb(var(--ds-border-default) / .55) 1px, transparent 1px), linear-gradient(90deg, rgb(var(--ds-border-default) / .55) 1px, transparent 1px); background-size: 48px 48px" />
        <StorefrontContainer>
            <div
                class="relative grid items-center gap-8 py-12 sm:py-16 lg:py-20"
                :class="variant === 'industrial_marketplace' ? 'lg:grid-cols-[minmax(0,1.25fr)_minmax(22rem,.75fr)]' : 'lg:grid-cols-2 lg:gap-14'"
            >
                <div :class="variant === 'split' && 'lg:py-8'">
                    <p v-if="section.heading.eyebrow" class="section-tag">{{ section.heading.eyebrow }}</p>
                    <h1 class="whitespace-pre-line font-display font-bold uppercase leading-[.95] tracking-tight text-content-primary" :class="variant === 'industrial_marketplace' ? 'text-4xl sm:text-6xl xl:text-7xl' : 'text-4xl sm:text-5xl xl:text-6xl'">
                        {{ section.heading.title }}
                    </h1>
                    <p v-if="section.heading.subtitle" class="mt-6 max-w-3xl text-lg font-semibold leading-7 text-content-secondary sm:text-xl">{{ section.heading.subtitle }}</p>
                    <p v-if="section.heading.description" class="mt-3 max-w-2xl leading-7 text-content-muted">{{ section.heading.description }}</p>
                    <div v-if="primaryCta || secondaryCta" class="mt-8 flex flex-wrap gap-3">
                        <UiButton v-if="primaryCta" :href="primaryCta.url" size="lg">{{ primaryCta.label }}</UiButton>
                        <UiButton v-if="secondaryCta" :href="secondaryCta.url" variant="outline" size="lg">{{ secondaryCta.label }}</UiButton>
                    </div>
                </div>
                <div v-if="section.config?.image_url" :class="variant === 'split' ? 'storefront-card overflow-hidden p-3 sm:p-5' : 'overflow-hidden rounded-xl border border-line bg-surface-card shadow-card'">
                    <ResponsiveImage :src="section.config.image_url" :alt="section.heading.title" :aspect="variant === 'split' ? '5/4' : '4/3'" object-fit="contain" loading="eager" />
                </div>
                <div v-else class="hidden min-h-80 rounded-xl border border-dashed border-line-strong bg-surface-card/65 lg:flex lg:items-center lg:justify-center">
                    <span class="font-display text-sm font-bold uppercase tracking-widest text-content-muted">Giải pháp công nghiệp</span>
                </div>
            </div>
        </StorefrontContainer>
    </section>
</template>
