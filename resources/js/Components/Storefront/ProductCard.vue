<script setup>
import { Link } from '@inertiajs/vue3';
import ResponsiveImage from './ResponsiveImage.vue';

defineProps({
    product: { type: Object, required: true },
    variant: { type: String, default: 'marketplace', validator: (value) => ['marketplace', 'compact', 'featured'].includes(value) },
});
</script>

<template>
    <article v-if="variant === 'featured'" class="group flex h-full flex-col overflow-hidden rounded-2xl border border-line bg-surface-card shadow-card transition duration-300 hover:-translate-y-1 hover:border-brand-border hover:shadow-card-hover">
        <Link :href="route('products.show', product.slug)" class="relative block overflow-hidden bg-surface-muted">
            <ResponsiveImage :src="product.image_url" :alt="product.name" aspect="16/10" />
            <div class="pointer-events-none absolute inset-x-0 bottom-0 h-1/2 bg-gradient-to-t from-surface-inverse/60 to-transparent opacity-80" />
            <div class="absolute inset-x-0 bottom-0 flex items-end justify-between gap-3 p-4">
                <span class="max-w-[72%] truncate rounded-full bg-surface-card/95 px-3 py-1.5 text-[0.6875rem] font-bold uppercase tracking-[0.14em] text-content-primary shadow-ds-sm">
                    {{ product.sku || 'Thiết bị công nghiệp' }}
                </span>
                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-brand text-lg font-semibold text-brand-contrast shadow-ds-sm transition group-hover:scale-110" aria-hidden="true">↗</span>
            </div>
        </Link>

        <div class="flex flex-1 flex-col p-5">
            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-content-muted">Cấu hình đề xuất</p>
            <h3 class="mt-2 min-h-14 font-display text-xl font-bold leading-snug text-content-primary">
                <Link :href="route('products.show', product.slug)" class="transition hover:text-brand-text">{{ product.name }}</Link>
            </h3>

            <dl v-if="product.card_specifications?.length" class="mt-4 overflow-hidden rounded-xl border border-line-subtle bg-surface-page/70 text-sm shadow-inner">
                <div v-for="(specification, index) in product.card_specifications.slice(0, 2)" :key="`${specification.key}-${specification.value}`" class="grid grid-cols-[minmax(0,0.82fr)_minmax(0,1.18fr)] items-start gap-3 px-3.5 py-3" :class="index && 'border-t border-line-subtle'">
                    <dt class="flex items-start gap-2 leading-5 text-content-secondary">
                        <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-brand" aria-hidden="true" />
                        <span>{{ specification.label || specification.key }}</span>
                    </dt>
                    <dd class="text-right font-semibold leading-5 text-content-primary">{{ specification.value }}</dd>
                </div>
            </dl>
            <div v-else class="mt-4 min-h-[4.625rem] rounded-xl border border-line-subtle bg-surface-page/70 px-3.5 py-3 text-sm leading-5 text-content-secondary shadow-inner">
                Liên hệ Phú Đức để nhận tư vấn cấu hình phù hợp.
            </div>

            <div class="mt-auto flex items-end justify-between gap-4 pt-5">
                <div>
                    <p class="text-xs font-medium text-content-muted">Giá tham khảo</p>
                    <p class="mt-1 font-display text-xl font-bold tracking-tight text-content-primary">{{ product.price_display }}</p>
                </div>
                <Link :href="route('products.show', product.slug)" class="inline-flex min-h-10 items-center rounded-lg bg-surface-inverse px-3.5 text-sm font-bold text-content-inverse transition hover:bg-content-primary">
                    Chi tiết
                </Link>
            </div>
        </div>
    </article>

    <article v-else class="group storefront-card-interactive overflow-hidden" :class="variant === 'compact' && 'sm:flex'">
        <Link :href="route('products.show', product.slug)" class="block" :class="variant === 'compact' && 'sm:w-2/5'">
            <ResponsiveImage :src="product.image_url" :alt="product.name" :aspect="variant === 'compact' ? '1/1' : '4/3'" />
        </Link>
        <div class="flex flex-1 flex-col p-5">
            <p v-if="product.sku" class="text-xs font-semibold uppercase tracking-wide text-content-muted">{{ product.sku }}</p>
            <h3 class="mt-1 font-display text-lg font-bold leading-snug text-content-primary">
                <Link :href="route('products.show', product.slug)" class="transition hover:text-brand-text">{{ product.name }}</Link>
            </h3>
            <dl v-if="product.card_specifications?.length" class="mt-3 space-y-1 text-sm text-content-secondary">
                <div v-for="specification in product.card_specifications.slice(0, variant === 'compact' ? 2 : 3)" :key="`${specification.key}-${specification.value}`" class="flex justify-between gap-3">
                    <dt>{{ specification.label || specification.key }}</dt>
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
