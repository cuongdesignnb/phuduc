<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({ section: Object, site: Object });
const secondaryUrl = () => props.section.config?.secondary_cta?.action === 'phone'
    ? `tel:${props.site.hotline || props.site.phone || ''}`
    : (props.section.config?.secondary_cta?.url || '#');
</script>

<template>
    <section class="border-b border-slate-200 bg-slate-50">
        <div class="mx-auto grid max-w-[1780px] items-center gap-10 px-5 py-12 lg:grid-cols-2 lg:px-8 lg:py-20">
            <div>
                <p v-if="section.heading.eyebrow" class="mb-3 text-sm font-black uppercase tracking-[.18em] text-amber-500">{{ section.heading.eyebrow }}</p>
                <h1 class="whitespace-pre-line text-4xl font-black uppercase leading-none text-slate-800 sm:text-6xl">{{ section.heading.title }}</h1>
                <p v-if="section.heading.subtitle" class="mt-5 max-w-2xl text-lg font-medium text-slate-600">{{ section.heading.subtitle }}</p>
                <p v-if="section.heading.description" class="mt-3 max-w-2xl text-slate-500">{{ section.heading.description }}</p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <Link v-if="section.config?.primary_cta?.label" :href="section.config.primary_cta.url || '#'" class="rounded-lg bg-[#ffd400] px-6 py-3 font-black text-slate-900">{{ section.config.primary_cta.label }}</Link>
                    <a v-if="section.config?.secondary_cta?.label" :href="secondaryUrl()" class="rounded-lg border border-slate-300 bg-white px-6 py-3 font-bold text-slate-700">{{ section.config.secondary_cta.label }}</a>
                </div>
            </div>
            <div v-if="section.config?.image_url" class="min-h-80 overflow-hidden rounded-2xl bg-white shadow-sm">
                <img :src="section.config.image_url" :alt="section.heading.title" class="h-full w-full object-contain" />
            </div>
        </div>
    </section>
</template>
