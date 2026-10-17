<script setup>
import StorefrontIcon from '@/Components/Storefront/StorefrontIcon.vue';

defineProps({ inverse: { type: Boolean, default: false } });
defineEmits(['prev', 'next']);
</script>

<template>
    <div class="flex items-center gap-2">
        <button
            v-for="direction in ['prev', 'next']"
            :key="direction"
            type="button"
            class="grid h-10 w-10 place-items-center rounded-full border transition hover:-translate-y-0.5"
            :class="inverse ? 'border-content-inverse/30 text-content-inverse hover:border-brand hover:bg-brand hover:text-brand-contrast' : 'border-line-strong bg-surface-card text-content-primary hover:border-brand-border hover:bg-brand hover:text-brand-contrast'"
            :aria-label="direction === 'prev' ? 'Xem mục trước' : 'Xem mục tiếp theo'"
            @click="$emit(direction)"
        >
            <StorefrontIcon :name="direction === 'prev' ? 'chevronLeft' : 'chevronRight'" class="h-5 w-5" />
        </button>
    </div>
</template>
