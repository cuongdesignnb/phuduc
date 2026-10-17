<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import HomeHeading from '../HomeHeading.vue';
import TrackArrows from '../TrackArrows.vue';
import StorefrontContainer from '@/Components/Storefront/StorefrontContainer.vue';
import { vReveal } from '@/Composables/useMotion';

const props = defineProps({ section: { type: Object, required: true } });
const active = ref(0);
const total = computed(() => props.section.items.length);
const current = computed(() => props.section.items[active.value] || props.section.items[0]);
const go = (direction) => { active.value = (active.value + direction + total.value) % total.value; };

let timer = null;
const paused = ref(false);
onMounted(() => {
    if (total.value < 2 || window.matchMedia?.('(prefers-reduced-motion: reduce)').matches) return;
    timer = window.setInterval(() => { if (!paused.value) go(1); }, 7000);
});
onBeforeUnmount(() => window.clearInterval(timer));
</script>

<template>
    <section v-if="section.items.length" class="storefront-section bg-surface-page">
        <StorefrontContainer>
            <div class="grid items-center gap-8 lg:grid-cols-[minmax(0,.8fr)_minmax(0,1.2fr)] lg:gap-14">
                <div>
                    <HomeHeading eyebrow="Khách hàng nói về chúng tôi" :title="section.heading.title" />
                    <div v-if="total > 1" class="mt-6 flex items-center gap-4">
                        <TrackArrows @prev="go(-1)" @next="go(1)" />
                        <div class="flex gap-1.5" aria-hidden="true">
                            <span v-for="(item, index) in section.items" :key="item.id" class="h-1.5 rounded-full transition-all duration-500" :class="index === active ? 'w-6 bg-brand' : 'w-1.5 bg-line-strong'" />
                        </div>
                    </div>
                </div>

                <div v-reveal="{ from: 'right' }" class="relative" @mouseenter="paused = true" @mouseleave="paused = false" @focusin="paused = true" @focusout="paused = false">
                    <svg class="absolute -top-5 right-6 h-16 w-16 text-brand/25" aria-hidden="true" viewBox="0 0 32 32" fill="currentColor"><path d="M13 7v8H8c0 4 2 6 5 7v3C7 24 4 20 4 14V7h9Zm15 0v8h-5c0 4 2 6 5 7v3c-6-1-9-5-9-11V7h9Z" /></svg>
                    <Transition mode="out-in" enter-active-class="transition duration-500 ease-out" enter-from-class="translate-x-4 opacity-0" leave-active-class="transition duration-300 ease-in" leave-to-class="-translate-x-4 opacity-0">
                        <blockquote :key="current.id" class="relative rounded-3xl border border-line bg-surface-card p-6 shadow-card sm:p-8" aria-live="polite">
                            <p class="text-base italic leading-7 text-content-secondary sm:text-lg sm:leading-8">“{{ current.description }}”</p>
                            <footer class="mt-6 flex items-center gap-4 border-t border-line-subtle pt-5">
                                <img v-if="current.image_url" :src="current.image_url" :alt="current.title" loading="lazy" class="h-14 w-14 shrink-0 rounded-full object-cover ring-4 ring-brand-soft">
                                <span v-else class="grid h-14 w-14 shrink-0 place-items-center rounded-full bg-brand font-display text-lg font-bold text-brand-contrast">{{ current.metadata?.avatar_text || current.title?.charAt(0) }}</span>
                                <span>
                                    <strong class="block font-display text-content-primary">{{ current.title }}</strong>
                                    <small v-if="current.subtitle" class="text-content-muted">{{ current.subtitle }}</small>
                                </span>
                            </footer>
                        </blockquote>
                    </Transition>
                </div>
            </div>
        </StorefrontContainer>
    </section>
</template>
