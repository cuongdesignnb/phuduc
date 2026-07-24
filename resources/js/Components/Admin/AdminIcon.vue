<script setup>
import { computed } from 'vue';

const props = defineProps({
    name: { type: String, default: 'dashboard' },
    size: { type: String, default: '20' },
});

const paths = {
    dashboard: 'M3 12l2-2 7-7 7 7m-14 0v10h5v-6h2v6h5V10',
    cube: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
    image: 'M4 5h16v14H4z m3 10 3-3 2 2 3-4 3 5M8 9h.01',
    cart: 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-2 3h12m-8 3h.01m7 0h.01',
    menu: 'M4 6h16M4 12h16M4 18h10',
    home: 'M3 10.5 12 3l9 7.5M5 9.75V21h14V9.75M9 21v-6h6v6',
    document: 'M7 3h7l5 5v13H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2m7 0v6h5M9 13h6m-6 4h6',
    folder: 'M3 7v10a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-6l-2-2H5a2 2 0 0 0-2 2',
    star: 'm12 3 2.1 5.1 5.4.5-4 3.7 1.2 5.4-4.7-2.8-4.7 2.8 1.2-5.4-4-3.7 5.4-.5L12 3z',
    shield: 'M12 3a12 12 0 0 0 7 3v5c0 4.4-2.9 8.1-7 9-4.1-.9-7-4.6-7-9V6a12 12 0 0 0 7-3m-3 8 2 2 4-4',
    cog: 'M12 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8m8 4-2 1 .1 2.2-2 1.2-1.7-1.4-2 .8-.3 2.1H10l-.3-2.1-2-.8L6 16.4l-2-1.2.1-2.2-2-1V10l2-1-.1-2.2 2-1.2 1.7 1.4 2-.8.3-2.1h2.2l.3 2.1 2 .8L18 5.6l2 1.2-.1 2.2 2 1v2z',
    chevron: 'm9 18 6-6-6-6',
    close: 'M6 6l12 12M18 6 6 18',
    user: 'M20 21a8 8 0 0 0-16 0m8-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8',
};

const path = computed(() => paths[props.name] || paths.dashboard);
</script>

<template>
    <svg :width="size" :height="size" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path :d="path" />
    </svg>
</template>
