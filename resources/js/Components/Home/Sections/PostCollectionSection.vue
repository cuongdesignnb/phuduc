<script setup>
import { Link } from '@inertiajs/vue3';
defineProps({ section: Object });
</script>

<template>
    <section v-if="section.items.length" class="mx-auto max-w-[1780px] px-5 py-10 lg:px-8">
        <div class="mb-5 flex items-center justify-between"><h2 class="text-2xl font-black text-slate-800">{{ section.heading.title }}</h2><Link :href="route('news.index')" class="text-sm font-bold text-amber-600">Xem tất cả →</Link></div>
        <div class="grid gap-5 md:grid-cols-3">
            <article v-for="post in section.items" :key="post.id" class="overflow-hidden rounded-xl border border-slate-200 bg-white">
                <img v-if="post.image_url" :src="post.image_url" :alt="post.title" class="h-48 w-full object-cover" />
                <div class="p-5"><small class="font-bold text-amber-600">{{ post.category }}</small><h3 class="mt-2 font-black text-slate-800"><Link :href="route('news.show', post.slug)">{{ post.title }}</Link></h3><p v-if="post.summary" class="mt-2 line-clamp-3 text-sm text-slate-500">{{ post.summary }}</p></div>
            </article>
        </div>
    </section>
</template>
