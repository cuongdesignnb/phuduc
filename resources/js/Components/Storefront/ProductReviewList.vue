<script setup>
import EmptyState from './EmptyState.vue';

defineProps({
    reviews: { type: Array, default: () => [] },
});
</script>

<template>
    <div class="space-y-3">
        <article v-for="review in reviews" :key="review.id" class="storefront-card p-4">
            <div class="flex flex-wrap items-center gap-3">
                <div class="grid h-9 w-9 place-items-center rounded-full bg-surface-muted font-bold text-content-primary">{{ review.initial }}</div>
                <h3 class="font-semibold text-content-primary">{{ review.customer_name }}</h3>
                <p class="text-sm text-content-muted">{{ review.rating }}/5</p>
                <time v-if="review.created_at" :datetime="review.created_at" class="ml-auto text-sm text-content-muted">{{ review.created_at_display }}</time>
            </div>
            <p class="mt-3 text-sm leading-6 text-content-secondary">{{ review.content }}</p>
        </article>
        <EmptyState v-if="!reviews.length" title="Chưa có đánh giá" description="Sản phẩm này chưa có đánh giá đã duyệt." />
    </div>
</template>
