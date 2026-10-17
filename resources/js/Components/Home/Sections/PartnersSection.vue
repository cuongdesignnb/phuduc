<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import StorefrontContainer from '@/Components/Storefront/StorefrontContainer.vue';

const props = defineProps({ section: { type: Object, required: true } });
const isExternal = (url) => /^(https?:)?\/\//.test(url || '');
const componentFor = (url) => !url ? 'div' : isExternal(url) ? 'a' : Link;
// Logos loop as a slow marquee once there are enough of them to fill a row.
const marquee = computed(() => props.section.items.length > 5);
const rows = computed(() => (marquee.value ? [...props.section.items, ...props.section.items] : props.section.items));
</script>

<template>
    <section v-if="section.items.length" class="border-y border-line-subtle bg-surface-card py-10 sm:py-12">
        <StorefrontContainer>
            <p class="mb-6 text-center font-display text-xs font-bold uppercase tracking-[0.2em] text-brand-text">{{ section.heading.title }}</p>
            <div :class="marquee && 'overflow-hidden [mask-image:linear-gradient(90deg,transparent,black_8%,black_92%,transparent)]'">
                <div :class="marquee ? 'flex w-max animate-marquee gap-4 hover:[animation-play-state:paused]' : 'grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5'">
                    <component
                        :is="componentFor(item.url)"
                        v-for="(item, index) in rows"
                        :key="`${item.id}-${index}`"
                        :href="item.url || undefined"
                        :target="isExternal(item.url) ? '_blank' : undefined"
                        :rel="isExternal(item.url) ? 'noopener noreferrer' : undefined"
                        :aria-hidden="marquee && index >= section.items.length ? 'true' : undefined"
                        :tabindex="marquee && index >= section.items.length ? -1 : undefined"
                        class="group grid min-h-20 place-items-center rounded-2xl border border-line-subtle bg-surface-page px-5 py-3 text-center font-display font-bold text-content-secondary transition hover:-translate-y-0.5 hover:border-brand-border hover:bg-surface-card hover:shadow-card"
                        :class="marquee && 'w-44 shrink-0'"
                    >
                        <img v-if="item.image_url" :src="item.image_url" :alt="item.title" loading="lazy" width="160" height="64" class="max-h-12 max-w-full object-contain opacity-70 grayscale transition group-hover:opacity-100 group-hover:grayscale-0">
                        <span v-else>{{ item.title }}</span>
                    </component>
                </div>
            </div>
        </StorefrontContainer>
    </section>
</template>
