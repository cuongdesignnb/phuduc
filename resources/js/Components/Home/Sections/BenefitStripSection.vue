<script setup>
import StorefrontContainer from '@/Components/Storefront/StorefrontContainer.vue';
import StorefrontIcon from '@/Components/Storefront/StorefrontIcon.vue';

defineProps({ section: { type: Object, required: true } });
</script>

<template>
    <section v-if="section.items.length" class="border-y border-line bg-surface-card">
        <StorefrontContainer>
            <div class="grid gap-px bg-line-subtle sm:grid-cols-2 lg:grid-cols-4">
                <div v-for="item in section.items" :key="item.id" class="flex min-h-20 items-center gap-3 bg-surface-card px-4 py-4 sm:px-5">
                    <span class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-brand-soft text-content-primary">
                        <StorefrontIcon :name="item.icon" class="h-5 w-5" />
                    </span>
                    <strong class="text-sm leading-5 text-content-primary">{{ item.title }}</strong>
                </div>
            </div>
        </StorefrontContainer>
    </section>
</template>
