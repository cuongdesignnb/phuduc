<script setup>
import { Link } from '@inertiajs/vue3';
import ResponsiveImage from './ResponsiveImage.vue';

defineProps({
    product: { type: Object, required: true },
    variant: { type: String, default: 'marketplace', validator: (value) => ['marketplace', 'compact'].includes(value) },
});
</script>

<template>
    <article class="group storefront-card-interactive overflow-hidden" :class="variant === 'compact' && 'sm:flex'">
        <Link :href="route('products.show', product.slug)" class="block" :class="variant === 'compact' && 'sm:w-2/5'">
            <ResponsiveImage :src="product.image_url" :alt="product.name" :aspect="variant === 'compact' ? '1/1' : '4/3'" />
        </Link>
        <div class="flex flex-1 flex-col p-5">
            <p v-if="product.sku" class="text-xs font-semibold uppercase tracking-wide text-content-muted">{{ product.sku }}</p>
            <h3 class="mt-1 font-display text-lg font-bold leading-snug text-content-primary">
                <Link :href="route('products.show', product.slug)" class="transition hover:text-brand-hover">{{ product.name }}</Link>
            </h3>
            <dl v-if="product.card_specifications?.length" class="mt-3 space-y-1 text-sm text-content-secondary">
                <div v-for="specification in product.card_specifications.slice(0, variant === 'compact' ? 2 : 3)" :key="`${specification.key}-${specification.value}`" class="flex justify-between gap-3">
                    <dt>{{ specification.key }}</dt>
                    <dd class="font-medium text-content-primary">{{ specification.value }}</dd>
                </div>
            </dl>
            <div class="mt-auto flex items-end justify-between gap-3 pt-4">
                <p class="font-display text-lg font-bold text-content-primary">{{ product.price_display }}</p>
                <p v-if="product.review_count" class="text-xs text-content-muted" :aria-label="`${product.average_rating} trên 5, ${product.review_count} đánh giá`">
                    {{ product.average_rating }}/5 · {{ product.review_count }}
                </p>
            </div>
        </div>
    </article>
</template>
