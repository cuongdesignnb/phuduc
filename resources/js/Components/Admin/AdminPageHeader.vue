<script setup>
defineProps({
    title: { type: String, required: true },
    description: { type: String, default: '' },
});
</script>

<template>
    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-admin-content">{{ title }}</h1>
            <p v-if="description" class="mt-1 text-sm text-admin-content-muted">{{ description }}</p>
        </div>
        <slot />
    </div>
</template>
