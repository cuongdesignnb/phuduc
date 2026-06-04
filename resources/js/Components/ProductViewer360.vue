<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    images: { type: Array, required: true },
});

const container = ref(null);
const currentIndex = ref(0);
const isDragging = ref(false);
const startX = ref(0);
const autoRotate = ref(true);
let autoRotateInterval = null;

const totalFrames = props.images.length;

const onMouseDown = (e) => {
    isDragging.value = true;
    startX.value = e.clientX || e.touches?.[0]?.clientX || 0;
    autoRotate.value = false;
    stopAutoRotate();
};

const onMouseMove = (e) => {
    if (!isDragging.value) return;
    const x = e.clientX || e.touches?.[0]?.clientX || 0;
    const diff = x - startX.value;
    if (Math.abs(diff) > 10) {
        const direction = diff > 0 ? 1 : -1;
        currentIndex.value = (currentIndex.value + direction + totalFrames) % totalFrames;
        startX.value = x;
    }
};

const onMouseUp = () => {
    isDragging.value = false;
};

const startAutoRotate = () => {
    if (autoRotateInterval) return;
    autoRotateInterval = setInterval(() => {
        if (autoRotate.value) {
            currentIndex.value = (currentIndex.value + 1) % totalFrames;
        }
    }, 100);
};

const stopAutoRotate = () => {
    if (autoRotateInterval) {
        clearInterval(autoRotateInterval);
        autoRotateInterval = null;
    }
};

onMounted(() => {
    if (totalFrames > 1) startAutoRotate();
});

onUnmounted(() => {
    stopAutoRotate();
});
</script>

<template>
    <div
        ref="container"
        class="relative select-none cursor-grab active:cursor-grabbing bg-gray-100 dark:bg-gray-700 rounded-lg overflow-hidden"
        @mousedown="onMouseDown"
        @mousemove="onMouseMove"
        @mouseup="onMouseUp"
        @mouseleave="onMouseUp"
        @touchstart="onMouseDown"
        @touchmove="onMouseMove"
        @touchend="onMouseUp"
    >
        <div class="aspect-square relative">
            <img
                v-for="(img, i) in images"
                :key="i"
                :src="'/storage/' + img.image_path"
                :class="i === currentIndex ? 'opacity-100' : 'opacity-0'"
                class="absolute inset-0 w-full h-full object-contain transition-opacity duration-100"
                draggable="false"
            />
        </div>

        <!-- 360 badge -->
        <div class="absolute top-3 left-3 bg-black/60 text-white text-xs px-2 py-1 rounded-full flex items-center gap-1">
            <svg class="w-4 h-4 animate-spin-slow" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            360°
        </div>

        <!-- Controls -->
        <div class="absolute bottom-3 left-1/2 -translate-x-1/2 flex gap-2">
            <button
                @click.stop="autoRotate = !autoRotate; autoRotate ? startAutoRotate() : stopAutoRotate()"
                class="bg-black/60 text-white text-xs px-3 py-1 rounded-full hover:bg-black/80 transition"
            >
                {{ autoRotate ? '⏸ Dừng' : '▶ Tự xoay' }}
            </button>
        </div>

        <p class="absolute bottom-3 right-3 text-xs text-gray-500 bg-white/80 dark:bg-gray-800/80 px-2 py-0.5 rounded">
            {{ currentIndex + 1 }}/{{ totalFrames }}
        </p>
    </div>
</template>
