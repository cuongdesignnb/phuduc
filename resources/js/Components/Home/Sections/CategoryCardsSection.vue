<script setup>
import { Link } from '@inertiajs/vue3';
defineProps({ section: Object });
const toneClass = (item) => ({
    cart: 'border-amber-300 bg-amber-50',
    crane: 'border-sky-300 bg-sky-50',
    forklift: 'border-emerald-300 bg-emerald-50',
}[item.metadata?.tone] || 'border-slate-200 bg-white');
</script>

<template>
    <section v-if="section.items.length" class="mx-auto max-w-[1780px] px-5 py-8 lg:px-8">
        <div class="mb-5">
            <h2 class="text-2xl font-black text-slate-800">{{ section.heading.title }}</h2>
            <p v-if="section.heading.subtitle" class="text-slate-500">{{ section.heading.subtitle }}</p>
        </div>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <Link v-for="item in section.items" :key="item.id" :href="item.url || route('products.index')" class="group flex min-h-32 items-center gap-4 rounded-xl border p-5 shadow-sm transition hover:-translate-y-1 hover:border-amber-300" :class="toneClass(item)">
                <img v-if="item.image_url" :src="item.image_url" :alt="item.title" class="h-20 w-24 rounded-lg object-contain" />
                <span v-else-if="item.icon" class="grid h-16 w-16 shrink-0 place-items-center rounded-lg bg-white/80 text-xs font-black uppercase text-slate-600">{{ item.icon }}</span>
                <div>
                    <h3 class="font-black text-slate-800">{{ item.title }}</h3>
                    <p v-if="item.subtitle" class="mt-1 text-sm text-slate-500">{{ item.subtitle }} →</p>
                </div>
            </Link>
        </div>
    </section>
</template>
