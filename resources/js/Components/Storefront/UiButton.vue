<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    href: { type: String, default: '' },
    type: { type: String, default: 'button' },
    variant: { type: String, default: 'primary', validator: (value) => ['primary', 'outline', 'ghost'].includes(value) },
    size: { type: String, default: 'md', validator: (value) => ['sm', 'md', 'lg'].includes(value) },
    disabled: { type: Boolean, default: false },
});

const isAnchor = computed(() => /^(https?:)?\/\//.test(props.href) || /^(tel|mailto):/.test(props.href));
const opensNewWindow = computed(() => /^(https?:)?\/\//.test(props.href));
const component = computed(() => {
    if (props.disabled && props.href) return 'span';
    if (props.href) return isAnchor.value ? 'a' : Link;
    return 'button';
});
const classes = computed(() => [
    `btn-${props.variant}`,
    props.size === 'sm' && 'min-h-10 px-4 py-2 text-xs',
    props.size === 'lg' && 'min-h-12 px-7 py-3.5 text-base',
    props.disabled && 'pointer-events-none opacity-55',
]);
</script>

<template>
    <component
        :is="component"
        :href="!disabled && href ? href : undefined"
        :type="!href ? type : undefined"
        :disabled="!href && disabled"
        :role="disabled && href ? 'link' : undefined"
        :aria-disabled="disabled && href ? 'true' : undefined"
        :tabindex="disabled && href ? -1 : undefined"
        :target="!disabled && opensNewWindow ? '_blank' : undefined"
        :rel="!disabled && opensNewWindow ? 'noopener noreferrer' : undefined"
        :class="classes"
        @click.capture="disabled ? $event.preventDefault() : undefined"
        @keydown.enter.capture="disabled ? $event.preventDefault() : undefined"
        @keydown.space.capture="disabled ? $event.preventDefault() : undefined"
    >
        <slot />
    </component>
</template>
