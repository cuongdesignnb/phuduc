<script setup>
import { Link } from '@inertiajs/vue3';
defineProps({ section: Object });
const toneClass = (item) => ({
    warehouse: 'border-amber-300',
    factory: 'border-sky-300',
    green: 'border-emerald-300',
}[item.metadata?.tone] || 'border-slate-200');
</script>

<template>
    <section v-if="section.items.length" class="mx-auto max-w-[1780px] px-5 py-10 lg:px-8">
        <h2 class="mb-5 text-2xl font-black text-slate-800">{{ section.heading.title }}</h2>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <Link v-for="item in section.items" :key="item.id" :href="item.url || route('products.index')" class="overflow-hidden rounded-xl border bg-white" :class="toneClass(item)">
                <img v-if="item.image_url" :src="item.image_url" :alt="item.title" class="h-36 w-full object-cover" />
                <strong class="block p-4 text-slate-700">{{ item.title }}</strong>
            </Link>
        </div>
    </section>
</template>
