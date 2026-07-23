<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';

const props = defineProps({
    frames: { type: Array, required: true },
});

const currentIndex = ref(0);
const isDragging = ref(false);
const startX = ref(0);
const isPlaying = ref(false);
const prefersReducedMotion = ref(false);
let rotateInterval = null;

const totalFrames = computed(() => props.frames.length);
const currentFrame = computed(() => props.frames[currentIndex.value] || null);

const move = (direction) => {
    if (totalFrames.value < 2) return;
    currentIndex.value = (currentIndex.value + direction + totalFrames.value) % totalFrames.value;
};

const stop = () => {
    isPlaying.value = false;
    if (rotateInterval) {
        clearInterval(rotateInterval);
        rotateInterval = null;
    }
};

const play = () => {
    if (totalFrames.value < 2 || prefersReducedMotion.value || rotateInterval) return;
    isPlaying.value = true;
    rotateInterval = setInterval(() => move(1), 160);
};

const toggle = () => {
    if (isPlaying.value) {
        stop();
        return;
    }
    play();
};

const onPointerDown = (event) => {
    if (totalFrames.value < 2) return;
    isDragging.value = true;
    startX.value = event.clientX;
    event.currentTarget.setPointerCapture?.(event.pointerId);
    stop();
};

const onPointerMove = (event) => {
    if (!isDragging.value) return;
    const diff = event.clientX - startX.value;
    if (Math.abs(diff) > 12) {
        move(diff > 0 ? 1 : -1);
        startX.value = event.clientX;
    }
};

const onPointerUp = () => {
    isDragging.value = false;
};

onMounted(() => {
    prefersReducedMotion.value = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
});

onUnmounted(() => {
    stop();
});
</script>

<template>
    <section
        class="relative select-none overflow-hidden rounded-lg border border-line bg-surface-muted"
        tabindex="0"
        aria-label="Trinh xem 360"
        @keydown.left.prevent="move(-1)"
        @keydown.right.prevent="move(1)"
        @pointerdown="onPointerDown"
        @pointermove="onPointerMove"
        @pointerup="onPointerUp"
        @pointercancel="onPointerUp"
    >
        <div class="relative aspect-square">
            <img
                v-if="currentFrame"
                :src="currentFrame.url"
                :alt="currentFrame.alt"
                class="absolute inset-0 h-full w-full object-contain"
                draggable="false"
            >
            <div v-else class="flex h-full w-full items-center justify-center text-sm text-content-muted">
                Khong co khung hinh 360
            </div>
        </div>
        <div class="absolute left-3 top-3 rounded-full bg-surface-card/90 px-3 py-1 text-xs font-semibold text-content-primary">
            360
        </div>
        <div v-if="totalFrames > 1" class="absolute bottom-3 left-1/2 flex -translate-x-1/2 items-center gap-2 rounded-full bg-surface-card/90 p-1 shadow-sm">
            <button type="button" class="rounded-full px-3 py-1 text-xs font-semibold text-content-primary hover:bg-surface-muted" :aria-label="isPlaying ? 'Dung xoay' : 'Tu xoay'" @click.stop="toggle">
                {{ isPlaying ? 'Dung' : 'Xoay' }}
            </button>
        </div>
        <p class="absolute bottom-3 right-3 rounded bg-surface-card/90 px-2 py-1 text-xs text-content-muted">
            {{ currentIndex + 1 }}/{{ totalFrames }}
        </p>
    </section>
</template>
