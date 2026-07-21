<script setup>
defineProps({ section: Object });
const toneClass = (item) => ({
    yellow: 'border-amber-400',
    blue: 'border-sky-400',
    green: 'border-emerald-400',
}[item.metadata?.tone] || 'border-slate-200');
</script>

<template>
    <section v-if="section.items.length" class="mx-auto max-w-[1780px] px-5 py-10 lg:px-8">
        <h2 class="mb-5 text-2xl font-black text-slate-800">{{ section.heading.title }}</h2>
        <div class="grid gap-4 md:grid-cols-3">
            <div v-for="item in section.items" :key="item.id" class="rounded-xl border-t-4 bg-white p-6 shadow-sm" :class="toneClass(item)"><strong class="text-3xl text-amber-500">{{ item.title }}</strong><h3 v-if="item.subtitle" class="mt-3 font-black text-slate-800">{{ item.subtitle }}</h3><p class="mt-2 text-sm leading-6 text-slate-500">{{ item.description }}</p></div>
        </div>
    </section>
</template>
