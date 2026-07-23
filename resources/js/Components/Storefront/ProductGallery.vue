<script setup>
import { computed, ref, watch } from 'vue';
import ResponsiveImage from './ResponsiveImage.vue';

const props = defineProps({
    images: { type: Array, default: () => [] },
    title: { type: String, default: '' },
});

const selected = ref(0);
const current = computed(() => props.images[selected.value] || null);

watch(() => props.images, () => {
    selected.value = 0;
});

const select = (index) => {
    selected.value = index;
};

const move = (direction) => {
    if (props.images.length < 2) return;
    selected.value = (selected.value + direction + props.images.length) % props.images.length;
};
</script>

<template>
    <section class="space-y-3" :aria-label="`Thư viện ảnh của ${title}`" @keydown.left.prevent="move(-1)" @keydown.right.prevent="move(1)">
        <ResponsiveImage :src="current?.url" :alt="current?.alt || title" aspect="1/1" object-fit="contain" loading="eager" />
        <div v-if="images.length > 1" class="grid grid-cols-5 gap-2">
            <button
                v-for="(image, index) in images"
                :key="image.id"
                type="button"
                class="overflow-hidden rounded-lg border"
                :class="selected === index ? 'border-brand ring-2 ring-brand-border' : 'border-line'"
                :aria-label="`Chọn hình ${index + 1}`"
                :aria-pressed="selected === index"
                @click="select(index)"
            >
                <ResponsiveImage :src="image.url" :alt="image.alt" aspect="1/1" />
            </button>
        </div>
    </section>
</template>
