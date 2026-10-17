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
    bolt: 'M13 2 4 14h7l-1 8 9-12h-7l1-8Z',
    leaf: 'M5 19c9 0 14-5 14-15C9 4 5 9 5 15v4Zm0 0 8-8',
    battery: 'M3 8h15v8H3V8Zm15 3h3v2h-3M6 11v2m3-2v2m3-2v2',
    gauge: 'M4 15a8 8 0 1 1 16 0M12 15l4-5M4 15h2m12 0h2',
    gear: 'M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm7.4-3a7.4 7.4 0 0 0-.1-1.2l2-1.6-2-3.4-2.4 1a7 7 0 0 0-2-1.2L14.5 3h-5l-.4 2.6a7 7 0 0 0-2 1.2l-2.4-1-2 3.4 2 1.6a7.4 7.4 0 0 0 0 2.4l-2 1.6 2 3.4 2.4-1a7 7 0 0 0 2 1.2l.4 2.6h5l.4-2.6a7 7 0 0 0 2-1.2l2.4 1 2-3.4-2-1.6c.1-.4.1-.8.1-1.2Z',
    chart: 'M4 20V10m6 10V4m6 16v-7m4 7H3',
    phone: 'M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2Z',
    home: 'm3 11 9-7 9 7M5 10v10h5v-6h4v6h5V10',
    grid: 'M4 4h7v7H4V4Zm9 0h7v7h-7V4ZM4 13h7v7H4v-7Zm9 0h7v7h-7v-7Z',
    menu: 'M4 7h16M4 12h10M4 17h16',
    search: 'm20 20-4.2-4.2M11 18a7 7 0 1 0 0-14 7 7 0 0 0 0 14Z',
    user: 'M15.75 7.5a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.25a7.5 7.5 0 0 1 15 0',
    news: 'M4 5h13v14H6a2 2 0 0 1-2-2V5Zm13 4h3v8a2 2 0 0 1-2 2M8 9h5M8 13h5',
    mail: 'M3.75 6.75h16.5v10.5H3.75V6.75Zm0 0 8.25 5.625L20.25 6.75',
    pin: 'M12 21s-7-6.2-7-11a7 7 0 1 1 14 0c0 4.8-7 11-7 11Zm0-8.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z',
    arrow: 'M5 12h14m-5-5 5 5-5 5',
    chevronLeft: 'm15 6-6 6 6 6',
    chevronRight: 'm9 6 6 6-6 6',
    close: 'M6 18 18 6M6 6l12 12',
    check: 'm5 12 5 5 9-10',
    factory: 'M3 21V10l6 4V10l6 4V5h6v16H3Zm4-4h2m4 0h2',
    clipboard: 'M9 4h6v3H9V4ZM7 5H5v16h14V5h-2M9 12h6m-6 4h4',
    tool: 'M14.7 6.3a4 4 0 0 0-5.4 5.4L3 18l3 3 6.3-6.3a4 4 0 0 0 5.4-5.4L15 12l-3-3 2.7-2.7Z',
    handshake: 'm11 17 2 2a1.5 1.5 0 0 0 2-2m-4 0-3-3m3 3a1.5 1.5 0 0 1-2 2l-4-4-2-1V7l3-1 4 1m6 10a1.5 1.5 0 0 0 2-2l-4-4m2 6-3-3m5 1 2-1V7l-3-1-5 2-2 2a1.5 1.5 0 0 0 2 2l2-1',
};
</script>

<template>
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" :aria-hidden="!label" :aria-label="label || undefined" role="img">
        <path :d="paths[name] || paths.shield" />
    </svg>
</template>
