<script setup>
defineProps({
    summary: { type: Object, required: true },
});
</script>

<template>
    <div class="storefront-card p-5">
        <h2 class="font-display text-xl font-bold text-content-primary">Đánh giá sản phẩm</h2>
        <div class="mt-4 flex flex-wrap items-end gap-4">
            <p class="font-display text-4xl font-bold text-content-primary">{{ summary.average_rating || 0 }}/{{ summary.best_rating || 5 }}</p>
            <p class="pb-1 text-sm text-content-secondary">{{ summary.count }} đánh giá đã duyệt</p>
        </div>
    </div>
</template>
