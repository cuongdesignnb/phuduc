<script setup>
defineProps({
    tone: { type: String, default: 'neutral', validator: (value) => ['neutral', 'success', 'warning', 'danger', 'info'].includes(value) },
});

const tones = {
    neutral: 'bg-surface-muted text-content-secondary',
    success: 'bg-success-soft text-success',
    warning: 'bg-warning-soft text-warning',
    danger: 'bg-danger-soft text-danger',
    info: 'bg-info-soft text-info',
};
</script>

<template>
    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold" :class="tones[tone]"><slot /></span>
</template>
