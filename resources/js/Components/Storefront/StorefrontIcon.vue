<script setup>
defineProps({
    name: { type: String, default: '' },
    label: { type: String, default: '' },
});

const paths = {
    shield: 'M12 3 5 6v5c0 4.5 2.8 8.2 7 10 4.2-1.8 7-5.5 7-10V6l-7-3Zm-3 9 2 2 4-4',
    award: 'm12 3 2.2 4.5 5 .7-3.6 3.5.9 5-4.5-2.4-4.5 2.4.9-5L4.8 8.2l5-.7L12 3Z',
    truck: 'M3 6h11v10H3V6Zm11 4h4l3 3v3h-7v-6ZM7 19a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm10 0a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z',
    headset: 'M4 13v-2a8 8 0 0 1 16 0v2M4 13h3v6H5a1 1 0 0 1-1-1v-5Zm16 0h-3v6h2a1 1 0 0 0 1-1v-5Zm-3 6c-1 2-3 2-5 2',
    cart: 'M3 4h2l2 11h10l3-8H6M9 20h.01M17 20h.01',
    crane: 'M5 21V4h11M8 7h11l-3-3M13 7v5l3 2v4',
    forklift: 'M4 4v12h7V8H8m3 8h6l2-3v-3m-1 8a2 2 0 1 0 0 4 2 2 0 0 0 0-4ZM7 18a2 2 0 1 0 0 4 2 2 0 0 0 0-4Z',
};
</script>

<template>
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" :aria-hidden="!label" :aria-label="label || undefined" role="img">
        <path :d="paths[name] || paths.shield" />
    </svg>
</template>
