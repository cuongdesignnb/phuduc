<script setup>
import { computed, nextTick, ref, watch } from 'vue';
import ResponsiveImage from './ResponsiveImage.vue';

const props = defineProps({
    images: { type: Array, default: () => [] },
    title: { type: String, default: '' },
    selectedId: { type: [Number, String], default: null },
});

const selected = ref(0);
const thumbnailStrip = ref(null);
const current = computed(() => props.images[selected.value] || null);

const scrollSelectedIntoView = () => {
    nextTick(() => thumbnailStrip.value?.querySelector(`[data-thumbnail-index="${selected.value}"]`)?.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' }));
};

const syncSelection = () => {
    const index = props.images.findIndex((image) => String(image.id) === String(props.selectedId));
    selected.value = index >= 0 ? index : 0;
    scrollSelectedIntoView();
};

watch(() => props.images, syncSelection, { deep: true, immediate: true });
watch(() => props.selectedId, syncSelection);

const select = (index) => {
    selected.value = index;
};

const move = (direction) => {
    if (props.images.length < 2) return;
    selected.value = (selected.value + direction + props.images.length) % props.images.length;
    scrollSelectedIntoView();
};

const scrollThumbnails = (direction) => {
    thumbnailStrip.value?.scrollBy({ left: direction * 280, behavior: 'smooth' });
};
</script>

<template>
    <section tabindex="0" class="space-y-3 outline-none" :aria-label="`Thư viện ảnh của ${title}`" @keydown.left.prevent="move(-1)" @keydown.right.prevent="move(1)">
        <ResponsiveImage :src="current?.url" :alt="current?.alt || title" aspect="1/1" object-fit="contain" loading="eager" />
        <div v-if="images.length > 1" class="flex items-center gap-2">
            <button type="button" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-line bg-surface-card text-xl text-content-primary transition hover:border-brand hover:text-brand" aria-label="Cuộn thumbnail sang trái" @click="scrollThumbnails(-1)">‹</button>
            <div ref="thumbnailStrip" class="flex min-w-0 flex-1 snap-x snap-mandatory gap-2 overflow-x-auto scroll-smooth pb-1" aria-label="Danh sách thumbnail sản phẩm">
                <button
                    v-for="(image, index) in images"
                    :key="image.id"
                    :data-thumbnail-index="index"
                    type="button"
                    class="h-20 w-20 shrink-0 snap-start overflow-hidden rounded-lg border bg-surface-card sm:h-24 sm:w-24"
                    :class="selected === index ? 'border-brand ring-2 ring-brand-border' : 'border-line'"
                    :aria-label="`Chọn hình ${index + 1}`"
                    :aria-pressed="selected === index"
                    @click="select(index)"
                >
                    <ResponsiveImage :src="image.url" :alt="image.alt" aspect="1/1" loading="eager" />
                </button>
            </div>
            <button type="button" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-line bg-surface-card text-xl text-content-primary transition hover:border-brand hover:text-brand" aria-label="Cuộn thumbnail sang phải" @click="scrollThumbnails(1)">›</button>
        </div>
    </section>
</template>
