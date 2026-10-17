<script setup>
import { Link } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import StorefrontContainer from '@/Components/Storefront/StorefrontContainer.vue';
import StorefrontIcon from '@/Components/Storefront/StorefrontIcon.vue';
import { vReveal } from '@/Composables/useMotion';

const props = defineProps({
    section: { type: Object, required: true },
    site: { type: Object, default: () => ({}) },
    // Items of the enabled benefit_strip section, shown as badges under the CTAs.
    highlights: { type: Array, default: () => [] },
    // Featured products with photos, shown when no hero image is configured.
    showcase: { type: Array, default: () => [] },
});
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
const isExternal = (url) => /^(https?:|tel:|mailto:|\/\/)/.test(url || '');

// Second half of the headline is highlighted with the brand colour, like the reference.
const titleParts = computed(() => {
    const title = (props.section.heading.title || '').trim();
    const lines = title.split(/\n+/).map((line) => line.trim()).filter(Boolean);
    if (lines.length > 1) return [lines.slice(0, -1).join(' '), lines.at(-1)];
    const amp = title.split(/\s+&\s+/);
    if (amp.length === 2) return [`${amp[0]} &`, amp[1]];
    const words = title.split(/\s+/);
    if (words.length < 4) return [title, ''];
    const cut = Math.ceil(words.length / 2);
    return [words.slice(0, cut).join(' '), words.slice(cut).join(' ')];
});
// All-caps headlines are softened to sentence case so Vietnamese diacritics stay legible.
const isAllCaps = computed(() => {
    const title = props.section.heading.title || '';
    return title.length > 12 && title === title.toLocaleUpperCase('vi') && title !== title.toLocaleLowerCase('vi');
});
const subtitlePoints = computed(() => (props.section.heading.subtitle || '')
    .split(/\s+[-–•·|]\s+|\n+/)
    .map((point) => point.trim())
    .filter(Boolean));
const badges = computed(() => props.highlights.slice(0, 4));

const hasImage = computed(() => !!props.section.config?.image_url);
const slides = computed(() => props.showcase.slice(0, 4));
const activeSlide = ref(0);
let timer = null;
onMounted(() => {
    if (hasImage.value || slides.value.length < 2 || window.matchMedia?.('(prefers-reduced-motion: reduce)').matches) return;
    timer = window.setInterval(() => { activeSlide.value = (activeSlide.value + 1) % slides.value.length; }, 4500);
});
onBeforeUnmount(() => window.clearInterval(timer));
const specIcon = (key) => ({ payload: 'forklift', range: 'gauge', battery: 'battery' }[key] || 'bolt');
</script>

<template>
    <section class="relative isolate overflow-hidden bg-surface-inverse text-content-inverse">
        <!-- Background -->
        <div class="absolute inset-0 -z-10" aria-hidden="true">
            <img
                v-if="hasImage"
                :src="section.config.image_url"
                alt=""
                loading="eager"
                fetchpriority="high"
                decoding="async"
                class="ev-kenburns h-full w-full object-cover"
                :class="variant === 'split' ? 'object-right' : 'object-center'"
            >
            <div v-else class="ev-dark-section h-full w-full">
                <div class="ev-grid-bg absolute inset-0 opacity-60" />
                <div class="absolute -right-40 top-1/2 h-[40rem] w-[40rem] -translate-y-1/2 rounded-full bg-brand/10 blur-3xl" />
            </div>
            <div v-if="hasImage" class="absolute inset-0 bg-gradient-to-r from-surface-inverse via-surface-inverse/75 to-surface-inverse/5" />
            <div class="absolute inset-x-0 bottom-0 h-32 bg-gradient-to-t from-surface-inverse/70 to-transparent" />
        </div>

        <StorefrontContainer>
            <div class="relative grid min-h-[34rem] items-center gap-10 pb-14 pt-10 sm:pt-14 lg:min-h-[40rem] lg:grid-cols-[minmax(0,1.05fr)_minmax(0,.95fr)] lg:pb-20">
                <div class="max-w-2xl">
                    <p v-if="section.heading.eyebrow" v-reveal class="mb-4 font-display text-xs font-bold uppercase tracking-[0.16em] text-brand">
                        {{ section.heading.eyebrow }}
                    </p>
                    <h1 v-reveal="{ delay: 80 }" class="font-display text-[2.1rem] font-extrabold leading-[1.12] tracking-tight sm:text-5xl xl:text-[3.6rem]" :class="isAllCaps && 'ev-sentence-case'">
                        <span class="block">{{ titleParts[0] }}</span>
                        <span v-if="titleParts[1]" class="block text-brand">{{ titleParts[1] }}</span>
                    </h1>
                    <ul v-if="subtitlePoints.length" v-reveal="{ delay: 160 }" class="mt-5 flex flex-wrap gap-x-3 gap-y-2 text-sm text-content-inverse/85 sm:text-[0.95rem]">
                        <li v-for="(point, index) in subtitlePoints" :key="point" class="inline-flex items-center gap-3">
                            <span v-if="index" class="h-1 w-1 rounded-full bg-brand" aria-hidden="true" />{{ point }}
                        </li>
                    </ul>
                    <p v-if="section.heading.description" v-reveal="{ delay: 200 }" class="mt-4 max-w-xl text-sm leading-6 text-content-inverse/70 sm:text-base">{{ section.heading.description }}</p>

                    <div v-if="primaryCta || secondaryCta" v-reveal="{ delay: 260 }" class="mt-8 flex flex-wrap gap-3">
                        <component
                            :is="isExternal(primaryCta.url) ? 'a' : Link"
                            v-if="primaryCta"
                            :href="primaryCta.url"
                            class="ev-shine group inline-flex min-h-12 items-center gap-3 rounded-full bg-brand px-7 font-display text-sm font-bold text-brand-contrast shadow-card transition hover:-translate-y-0.5 hover:bg-brand-hover"
                        >
                            {{ primaryCta.label }}
                            <StorefrontIcon name="arrow" class="h-4 w-4 transition group-hover:translate-x-1" />
                        </component>
                        <component
                            :is="isExternal(secondaryCta.url) ? 'a' : Link"
                            v-if="secondaryCta"
                            :href="secondaryCta.url"
                            class="group inline-flex min-h-12 items-center gap-3 rounded-full border border-content-inverse/50 px-6 font-display text-sm font-bold text-content-inverse transition hover:-translate-y-0.5 hover:border-brand hover:text-brand"
                        >
                            <span class="h-4 w-4 rounded-full border-2 border-current" aria-hidden="true" />
                            {{ secondaryCta.label }}
                            <StorefrontIcon name="arrow" class="h-4 w-4 transition group-hover:translate-x-1" />
                        </component>
                    </div>

                    <!-- Benefit badges (benefit strip data) -->
                    <ul v-if="badges.length" class="mt-10 grid max-w-xl grid-cols-2 gap-x-5 gap-y-4 sm:flex sm:flex-wrap sm:gap-x-7">
                        <li v-for="(item, index) in badges" :key="item.id" v-reveal="{ delay: 320 + index * 80 }" class="flex items-center gap-2.5">
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full border-2 border-brand/80 text-brand">
                                <StorefrontIcon :name="item.icon" class="h-[1.15rem] w-[1.15rem]" />
                            </span>
                            <span class="max-w-[7.5rem] text-xs font-semibold leading-snug text-content-inverse/90">{{ item.title }}</span>
                        </li>
                    </ul>
                </div>

                <!-- Product showcase when no hero photo is configured -->
                <div v-if="!hasImage && slides.length" v-reveal="{ from: 'right', delay: 200 }" class="relative mx-auto w-full max-w-xl lg:mx-0">
                    <div class="pointer-events-none absolute inset-x-6 bottom-2 h-10 rounded-[100%] bg-brand/30 blur-2xl" aria-hidden="true" />
                    <div class="relative aspect-[5/4] overflow-hidden rounded-[2rem] border border-content-inverse/15 bg-surface-card shadow-card-hover">
                        <Link
                            v-for="(product, index) in slides"
                            :key="product.id"
                            :href="route('products.show', product.slug)"
                            class="absolute inset-0 block transition-all duration-1000 ease-out"
                            :class="index === activeSlide ? 'scale-100 opacity-100' : 'pointer-events-none scale-105 opacity-0'"
                            :aria-hidden="index !== activeSlide"
                            :tabindex="index === activeSlide ? undefined : -1"
                        >
                            <img :src="product.image_url" :alt="product.name" :loading="index ? 'lazy' : 'eager'" decoding="async" class="h-full w-full object-cover">
                        </Link>
                        <div class="pointer-events-none absolute inset-x-0 bottom-0 bg-gradient-to-t from-surface-inverse/90 via-surface-inverse/40 to-transparent px-5 pb-5 pt-20">
                            <Transition mode="out-in" enter-active-class="transition duration-500 ease-out" enter-from-class="translate-y-3 opacity-0" leave-active-class="transition duration-300 ease-in" leave-to-class="opacity-0">
                                <div :key="slides[activeSlide].id">
                                    <strong class="line-clamp-2 block font-display text-lg font-bold text-content-inverse sm:text-xl">{{ slides[activeSlide].name }}</strong>
                                    <span v-if="slides[activeSlide].card_specifications?.length" class="mt-2 flex flex-wrap gap-2">
                                        <span v-for="spec in slides[activeSlide].card_specifications.slice(0, 2)" :key="spec.key" class="inline-flex max-w-full items-center gap-1.5 rounded-full bg-content-inverse/15 px-2.5 py-1 text-xs text-content-inverse backdrop-blur">
                                            <StorefrontIcon :name="specIcon(spec.key)" class="h-3.5 w-3.5 shrink-0 text-brand" /><span class="truncate">{{ spec.value }}</span>
                                        </span>
                                    </span>
                                </div>
                            </Transition>
                        </div>
                    </div>
                    <!-- floating energy card -->
                    <div class="ev-float absolute -left-4 top-6 hidden items-center gap-3 rounded-2xl bg-surface-card/95 p-3 pr-4 text-content-primary shadow-card backdrop-blur sm:flex">
                        <span class="relative flex h-10 w-6 items-end overflow-hidden rounded-md border-2 border-success p-0.5" aria-hidden="true">
                            <span class="ev-charge-fill block h-full w-full rounded-sm bg-success" />
                        </span>
                        <span class="text-xs font-bold leading-tight">100% điện<br><span class="font-medium text-content-muted">Không phát thải</span></span>
                    </div>
                    <div v-if="slides.length > 1" class="absolute -bottom-6 left-1/2 flex -translate-x-1/2 gap-1.5">
                        <button
                            v-for="(product, index) in slides"
                            :key="product.id"
                            type="button"
                            class="h-1.5 rounded-full transition-all duration-500"
                            :class="index === activeSlide ? 'w-7 bg-brand' : 'w-1.5 bg-content-inverse/40'"
                            :aria-label="`Xem ${product.name}`"
                            :aria-current="index === activeSlide"
                            @click="activeSlide = index"
                        />
                    </div>
                </div>
            </div>
        </StorefrontContainer>

        <!-- Energy flow line -->
        <div class="absolute inset-x-0 bottom-0 h-0.5 bg-content-inverse/10" aria-hidden="true">
            <div class="ev-flow-line h-full w-full" />
        </div>
    </section>
</template>
