<script setup>
defineProps({ section: Object });
const iconSymbols = { shield: '◆', award: '★', truck: '▰', headset: '●' };
</script>

<template>
    <section v-if="section.items.length" class="border-y border-slate-200 bg-white">
        <div class="mx-auto grid max-w-[1780px] gap-3 px-5 py-5 sm:grid-cols-2 lg:grid-cols-4 lg:px-8">
            <div v-for="item in section.items" :key="item.id" class="flex items-center gap-3 rounded-lg bg-slate-50 px-4 py-3">
                <span class="grid h-10 w-10 place-items-center rounded-full bg-[#ffd400] font-black text-slate-900">{{ iconSymbols[item.icon] || item.icon || '✓' }}</span>
                <strong class="text-sm text-slate-700">{{ item.title }}</strong>
            </div>
        </div>
    </section>
</template>
