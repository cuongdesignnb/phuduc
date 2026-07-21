<script setup>
defineProps({
    src: { type: String, default: '' },
    alt: { type: String, default: '' },
    loading: { type: String, default: 'lazy' },
    aspect: { type: String, default: '4/3' },
    objectFit: { type: String, default: 'cover', validator: (value) => ['cover', 'contain'].includes(value) },
    width: { type: [String, Number], default: undefined },
    height: { type: [String, Number], default: undefined },
});
</script>

<template>
    <div class="relative overflow-hidden bg-surface-muted" :style="{ aspectRatio: aspect }">
        <img
            v-if="src"
            :src="src"
            :alt="alt"
            :loading="loading"
            decoding="async"
            :width="width"
            :height="height"
            class="h-full w-full"
            :class="objectFit === 'contain' ? 'object-contain' : 'object-cover'"
        >
        <div v-else class="flex h-full w-full items-center justify-center text-sm font-medium text-content-muted" aria-hidden="true">
            Không có hình ảnh
        </div>
    </div>
</template>
