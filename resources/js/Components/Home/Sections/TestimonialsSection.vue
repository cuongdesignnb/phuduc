<script setup>
defineProps({ section: Object });
</script>

<template>
    <section v-if="section.items.length" class="bg-slate-100 py-10">
        <div class="mx-auto max-w-[1780px] px-5 lg:px-8">
            <h2 class="mb-5 text-2xl font-black text-slate-800">{{ section.heading.title }}</h2>
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <blockquote v-for="item in section.items" :key="item.id" class="rounded-xl bg-white p-6 shadow-sm">
                    <p class="text-slate-600">“{{ item.description }}”</p>
                    <div class="mt-5 flex items-center gap-3">
                        <img v-if="item.image_url" :src="item.image_url" :alt="item.title" class="h-11 w-11 rounded-full object-cover" />
                        <span v-else class="grid h-11 w-11 place-items-center rounded-full bg-[#ffd400] font-black">{{ item.metadata?.avatar_text || item.title?.charAt(0) }}</span>
                        <div><strong class="block text-slate-800">{{ item.title }}</strong><small class="text-slate-500">{{ item.subtitle }}</small></div>
                    </div>
                </blockquote>
            </div>
        </div>
    </section>
</template>
