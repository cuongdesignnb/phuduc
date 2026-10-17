<script setup>
import HomeHeading from '../HomeHeading.vue';
import StorefrontContainer from '@/Components/Storefront/StorefrontContainer.vue';
import StorefrontIcon from '@/Components/Storefront/StorefrontIcon.vue';
import { vReveal } from '@/Composables/useMotion';

defineProps({ section: { type: Object, required: true } });
const stepIcons = ['headset', 'search', 'clipboard', 'tool', 'handshake', 'award'];
</script>

<template>
    <section v-if="section.items.length" class="relative overflow-hidden bg-surface-card">
        <div class="pointer-events-none absolute -right-24 top-0 hidden h-full w-[38%] skew-x-[-12deg] bg-gradient-to-br from-brand-soft to-surface-muted lg:block" aria-hidden="true" />
        <StorefrontContainer class="relative">
            <div class="storefront-section">
                <HomeHeading eyebrow="Quy trình đồng hành" :title="section.heading.title" />

                <ol class="relative mt-10 grid gap-4 sm:grid-cols-2 lg:flex lg:gap-0">
                    <li
                        v-for="(item, index) in section.items"
                        :key="item.id"
                        v-reveal="{ delay: index * 110 }"
                        class="group relative flex gap-4 rounded-2xl border border-line bg-surface-card p-4 shadow-ds-sm transition hover:-translate-y-1 hover:border-brand-border hover:shadow-card lg:flex-1 lg:flex-col lg:border-0 lg:bg-transparent lg:p-0 lg:pr-6 lg:shadow-none lg:hover:translate-y-0 lg:hover:shadow-none"
                    >
                        <!-- connector -->
                        <span v-if="index < section.items.length - 1" class="absolute left-16 right-0 top-7 hidden h-0.5 overflow-hidden bg-line lg:block" aria-hidden="true">
                            <span class="ev-flow-line block h-full w-full" :style="{ animationDelay: `${index * 0.6}s` }" />
                        </span>
                        <span class="relative z-10 grid h-14 w-14 shrink-0 place-items-center rounded-full bg-brand text-brand-contrast shadow-card ring-8 ring-surface-card transition duration-300 group-hover:scale-110">
                            <StorefrontIcon :name="stepIcons[index % stepIcons.length]" class="h-6 w-6" />
                        </span>
                        <div class="min-w-0 lg:mt-4">
                            <span class="font-display text-sm font-bold text-brand-text">{{ item.title || String(index + 1).padStart(2, '0') }}.</span>
                            <h3 v-if="item.subtitle" class="mt-0.5 font-display text-base font-bold leading-snug text-content-primary sm:text-lg">{{ item.subtitle }}</h3>
                            <p v-if="item.description" class="mt-1.5 text-sm leading-6 text-content-secondary">{{ item.description }}</p>
                        </div>
                    </li>
                </ol>
            </div>
        </StorefrontContainer>
    </section>
</template>
