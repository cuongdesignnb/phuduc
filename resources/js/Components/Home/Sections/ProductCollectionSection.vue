<script setup>
import { Link } from '@inertiajs/vue3';
defineProps({ section: Object });
</script>

<template>
    <section v-if="section.items.length" class="mx-auto max-w-[1780px] px-5 py-10 lg:px-8">
        <div class="mb-5 flex items-end justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-slate-800">{{ section.heading.title }}</h2>
                <p v-if="section.heading.subtitle" class="text-slate-500">{{ section.heading.subtitle }}</p>
            </div>
            <Link :href="route('products.index')" class="text-sm font-bold text-amber-600">Xem tất cả →</Link>
        </div>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <article v-for="product in section.items" :key="product.id" class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <Link :href="route('products.show', product.slug)">
                    <div class="grid aspect-[1.25] place-items-center overflow-hidden rounded-lg bg-slate-50">
                        <img v-if="product.image_url" :src="product.image_url" :alt="product.name" class="h-full w-full object-contain" />
                    </div>
                    <h3 class="mt-4 min-h-12 font-black leading-snug text-slate-800">{{ product.name }}</h3>
                </Link>
                <ul v-if="product.card_specifications.length" class="mt-2 space-y-1 text-sm text-slate-500">
                    <li v-for="spec in product.card_specifications" :key="spec.key">• {{ spec.label }}: {{ spec.value }}</li>
                </ul>
                <div class="mt-3 text-sm text-slate-500">
                    <span v-if="product.review_count">★ {{ product.average_rating }} ({{ product.review_count }} đánh giá)</span>
                    <span v-else>Chưa có đánh giá</span>
                </div>
                <div class="mt-2 text-lg font-black text-slate-900">{{ product.price_display }}</div>
            </article>
        </div>
    </section>
</template>
