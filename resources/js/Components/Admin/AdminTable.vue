<script setup>
defineProps({ columns: { type: Array, default: () => [] } });
</script>

<template>
    <div class="overflow-x-auto">
        <table class="w-full min-w-[40rem] text-left text-sm">
            <thead><tr class="border-b border-white/10 text-xs uppercase text-carbon-500"><th v-for="column in columns" :key="column.key" scope="col" class="px-3 py-3">{{ column.label }}</th></tr></thead>
            <tbody><slot /></tbody>
        </table>
    </div>
</template>
