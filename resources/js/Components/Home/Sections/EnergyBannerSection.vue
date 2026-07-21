<script setup>
defineProps({ section: Object });
</script>

<template>
    <section class="mx-auto max-w-[1780px] px-5 py-8 lg:px-8">
        <div class="grid gap-8 overflow-hidden rounded-2xl bg-slate-800 p-8 text-white lg:grid-cols-[1fr_auto] lg:p-12">
            <div>
                <p v-if="section.heading.eyebrow" class="font-black uppercase tracking-widest text-[#ffd400]">{{ section.heading.eyebrow }}</p>
                <h2 class="mt-2 text-3xl font-black">{{ section.heading.title }}</h2>
                <p v-if="section.heading.description" class="mt-3 max-w-3xl text-slate-300">{{ section.heading.description }}</p>
                <div v-if="section.config?.stats?.length" class="mt-6 flex flex-wrap gap-8">
                    <div v-for="(stat, index) in section.config.stats" :key="index"><strong class="block text-2xl text-[#ffd400]">{{ stat.value }}</strong><span class="text-sm text-slate-300">{{ stat.label }}</span></div>
                </div>
            </div>
            <img v-if="section.config?.image_url" :src="section.config.image_url" :alt="section.heading.title" class="max-h-56 rounded-xl object-contain" />
        </div>
    </section>
</template>
