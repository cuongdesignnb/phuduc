<script setup>
defineProps({ section: Object });
</script>

<template>
    <section v-if="section.items.length" class="mx-auto max-w-[1780px] px-5 py-10 lg:px-8">
        <h2 class="mb-5 text-2xl font-black text-slate-800">{{ section.heading.title }}</h2>
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
            <a v-for="item in section.items" :key="item.id" :href="item.url || '#'" class="grid h-24 place-items-center rounded-xl border border-slate-200 bg-white p-4 text-center font-black text-slate-500">
                <img v-if="item.image_url" :src="item.image_url" :alt="item.title" class="max-h-14 max-w-full object-contain" />
                <span v-else>{{ item.title }}</span>
            </a>
        </div>
    </section>
</template>
