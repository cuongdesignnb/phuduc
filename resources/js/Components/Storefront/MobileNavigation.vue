<script setup>
import { Link } from '@inertiajs/vue3';
import { nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import StorefrontSearch from './StorefrontSearch.vue';

const FOCUSABLE_SELECTOR = [
    'a[href]',
    'button:not([disabled])',
    'input:not([disabled])',
    'select:not([disabled])',
    'textarea:not([disabled])',
    '[tabindex]:not([tabindex="-1"])',
].join(', ');

const props = defineProps({
    open: { type: Boolean, default: false },
    items: { type: Array, default: () => [] },
    searchKeyword: { type: String, default: '' },
});
const emit = defineEmits(['close', 'update:searchKeyword', 'search']);
const drawer = ref(null);
const closeButton = ref(null);
const expanded = ref(new Set());
let previousOverflow = '';
const itemKey = (item) => item.id || item.label;
const isExternal = (url) => /^(https?:)?\/\//.test(url || '');
const linkComponent = (url) => (isExternal(url) ? 'a' : Link);
const toggle = (item) => {
    const next = new Set(expanded.value);
    next.has(itemKey(item)) ? next.delete(itemKey(item)) : next.add(itemKey(item));
    expanded.value = next;
};

const getFocusableElements = () => {
    if (!drawer.value) return [];
    return [...drawer.value.querySelectorAll(FOCUSABLE_SELECTOR)].filter((el) => {
        if (el.offsetParent === null) return false;
        const style = window.getComputedStyle(el);
        return style.visibility !== 'hidden' && style.display !== 'none';
    });
};

const onKeydown = (event) => {
    if (!props.open) return;

    if (event.key === 'Escape') {
        emit('close');
        return;
    }

    if (event.key === 'Tab') {
        const focusable = getFocusableElements();
        if (focusable.length === 0) return;

        const first = focusable[0];
        const last = focusable[focusable.length - 1];

        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    }
};

watch(() => props.open, async (open) => {
    if (open) {
        previousOverflow = document.body.style.overflow;
        document.body.style.overflow = 'hidden';
        await nextTick();
        closeButton.value?.focus();
    } else {
        document.body.style.overflow = previousOverflow;
    }
}, { immediate: true });

onMounted(() => {
    document.addEventListener('keydown', onKeydown);
});
onBeforeUnmount(() => {
    document.removeEventListener('keydown', onKeydown);
    document.body.style.overflow = previousOverflow;
});
</script>

<template>
    <Transition enter-active-class="transition duration-200" enter-from-class="opacity-0" leave-active-class="transition duration-150" leave-to-class="opacity-0">
        <div v-if="open" class="fixed inset-0 z-[70] bg-surface-inverse/45 lg:hidden" @click.self="$emit('close')">
            <aside ref="drawer" id="mobile-navigation-dialog" class="ml-auto flex h-dvh w-full max-w-md flex-col bg-surface-card" role="dialog" aria-modal="true" aria-labelledby="mobile-navigation-title">
                <div class="flex items-center justify-between border-b border-line px-4 py-3">
                    <h2 id="mobile-navigation-title" class="font-display text-xl font-bold">Menu</h2>
                    <button ref="closeButton" type="button" class="grid min-h-11 min-w-11 place-items-center rounded-lg bg-surface-muted" aria-label="Đóng menu" @click="$emit('close')">
                        <svg class="h-6 w-6" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <div class="border-b border-line p-4">
                    <StorefrontSearch id="mobile-storefront-search" :model-value="searchKeyword" compact @update:model-value="$emit('update:searchKeyword', $event)" @submit="$emit('search', $event)" />
                </div>
                <nav aria-label="Điều hướng mobile" class="min-h-0 flex-1 overflow-y-auto overscroll-contain p-3">
                    <ul class="space-y-1">
                        <li v-for="item in items" :key="itemKey(item)" class="rounded-lg">
                            <div class="flex items-center">
                                <component
                                    :is="linkComponent(item.url)"
                                    v-if="item.url"
                                    :href="item.url"
                                    :target="isExternal(item.url) ? '_blank' : undefined"
                                    :rel="isExternal(item.url) ? 'noopener noreferrer' : undefined"
                                    class="flex min-h-12 flex-1 items-center rounded-lg px-3 font-semibold text-content-primary hover:bg-surface-muted"
                                    @click="$emit('close')"
                                >{{ item.label }}</component>
                                <span v-else class="flex min-h-12 flex-1 items-center px-3 font-semibold">{{ item.label }}</span>
                                <button
                                    v-if="item.children?.length"
                                    type="button"
                                    class="grid min-h-12 min-w-12 place-items-center rounded-lg hover:bg-surface-muted"
                                    :aria-label="`Mở menu ${item.label}`"
                                    :aria-expanded="expanded.has(itemKey(item))"
                                    :aria-controls="`mobile-menu-${itemKey(item)}`"
                                    @click="toggle(item)"
                                >
                                    <svg class="h-5 w-5 transition" :class="expanded.has(itemKey(item)) && 'rotate-180'" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 9 6 6 6-6" /></svg>
                                </button>
                            </div>
                            <ul v-if="item.children?.length && expanded.has(itemKey(item))" :id="`mobile-menu-${itemKey(item)}`" class="ml-4 border-l border-line pl-3">
                                <li v-for="child in item.children" :key="itemKey(child)">
                                    <div class="flex items-center">
                                        <component
                                            :is="linkComponent(child.url)"
                                            v-if="child.url"
                                            :href="child.url"
                                            :target="isExternal(child.url) ? '_blank' : undefined"
                                            :rel="isExternal(child.url) ? 'noopener noreferrer' : undefined"
                                            class="flex min-h-11 flex-1 items-center rounded-lg px-3 text-sm text-content-secondary hover:bg-surface-muted"
                                            @click="$emit('close')"
                                        >{{ child.label }}</component>
                                        <span v-else class="flex min-h-11 flex-1 items-center px-3 text-sm font-semibold">{{ child.label }}</span>
                                        <button v-if="child.children?.length" type="button" class="grid min-h-11 min-w-11 place-items-center" :aria-label="`Mở menu ${child.label}`" :aria-expanded="expanded.has(itemKey(child))" :aria-controls="`mobile-submenu-${itemKey(child)}`" @click="toggle(child)">+</button>
                                    </div>
                                    <ul v-if="child.children?.length && expanded.has(itemKey(child))" :id="`mobile-submenu-${itemKey(child)}`" class="ml-3 border-l border-line-subtle pl-3">
                                        <li v-for="grandchild in child.children.filter((entry) => entry.url)" :key="itemKey(grandchild)">
                                            <component
                                                :is="linkComponent(grandchild.url)"
                                                :href="grandchild.url"
                                                :target="isExternal(grandchild.url) ? '_blank' : undefined"
                                                :rel="isExternal(grandchild.url) ? 'noopener noreferrer' : undefined"
                                                class="block rounded-lg px-3 py-2.5 text-sm text-content-secondary hover:bg-surface-muted"
                                                @click="$emit('close')"
                                            >{{ grandchild.label }}</component>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </nav>
            </aside>
        </div>
    </Transition>
</template>
