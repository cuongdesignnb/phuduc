<script setup>
import StorefrontContainer from '@/Components/Storefront/StorefrontContainer.vue';
import StorefrontIcon from '@/Components/Storefront/StorefrontIcon.vue';
import { vReveal } from '@/Composables/useMotion';

defineProps({ section: { type: Object, required: true } });
</script>

<template>
    <section v-if="section.items.length" class="border-b border-line bg-surface-card">
        <StorefrontContainer>
            <ul class="grid grid-cols-2 gap-px bg-line-subtle lg:grid-cols-4">
                <li v-for="(item, index) in section.items" :key="item.id" v-reveal="{ delay: index * 80 }" class="group flex min-h-20 items-center gap-3 bg-surface-card px-3 py-4 sm:px-5">
                    <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-brand-soft text-brand-text transition group-hover:bg-brand group-hover:text-brand-contrast">
                        <StorefrontIcon :name="item.icon" class="h-5 w-5" />
                    </span>
                    <strong class="text-sm leading-5 text-content-primary">{{ item.title }}</strong>
                </li>
            </ul>
        </StorefrontContainer>
    </section>
</template>
