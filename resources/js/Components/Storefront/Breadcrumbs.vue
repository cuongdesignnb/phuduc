<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    items: { type: Array, default: () => [] },
    variant: { type: String, default: 'pill', validator: (value) => ['pill', 'minimal'].includes(value) },
});
</script>

<template>
    <nav
        aria-label="Điều hướng phân cấp"
        class="max-w-full"
        :class="variant === 'pill' ? 'inline-flex rounded-full border border-line bg-surface-card/90 p-1 shadow-ds-sm' : 'block'"
    >
        <ol class="flex max-w-full items-center overflow-hidden text-xs font-medium text-content-secondary sm:text-sm">
            <li
                v-for="(item, index) in items"
                :key="`${item.name || item.label}-${index}`"
                class="flex min-w-0 items-center"
                :class="index > 0 && index < items.length - 1 && 'hidden sm:flex'"
            >
                <span v-if="index" class="h-1.5 w-1.5 shrink-0 rotate-45 border-r border-t border-line-strong" :class="variant === 'pill' ? 'mx-1.5 sm:mx-2' : 'mx-2'" aria-hidden="true" />
                <Link
                    v-if="(item.url || item.href) && index < items.length - 1"
                    :href="item.url || item.href"
                    class="transition hover:text-brand-text focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand/40"
                    :class="variant === 'pill' ? 'rounded-full px-2.5 py-1.5 hover:bg-brand-soft' : 'rounded px-0.5 py-0.5'"
                >
                    {{ item.name || item.label }}
                </Link>
                <span
                    v-else
                    class="block max-w-[14rem] truncate font-semibold text-content-primary sm:max-w-[26rem]"
                    :class="variant === 'pill' ? 'rounded-full bg-surface-muted px-2.5 py-1.5' : 'px-0.5 py-0.5'"
                    :aria-current="index === items.length - 1 ? 'page' : undefined"
                    :title="item.name || item.label"
                >
                    {{ item.name || item.label }}
                </span>
            </li>
        </ol>
    </nav>
</template>
