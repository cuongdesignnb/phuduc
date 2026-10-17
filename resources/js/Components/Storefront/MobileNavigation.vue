<script setup>
import { Link } from '@inertiajs/vue3';
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import StorefrontIcon from './StorefrontIcon.vue';
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
    site: { type: Object, default: () => ({}) },
    currentPath: { type: String, default: '/' },
    accountHref: { type: String, default: '' },
    accountLabel: { type: String, default: '' },
});
const emit = defineEmits(['close', 'update:searchKeyword', 'search']);
const drawer = ref(null);
const closeButton = ref(null);
const expanded = ref(new Set());
let previousOverflow = '';
const itemKey = (item) => item.id || item.label;
const isExternal = (url) => /^(https?:)?\/\//.test(url || '');
const linkComponent = (url) => (isExternal(url) ? 'a' : Link);
const contactPhone = computed(() => props.site.hotline || props.site.phone || '');
const pathOf = (url) => {
    try {
        return new URL(url, window.location.origin).pathname.replace(/\/$/, '') || '/';
    } catch {
        return '';
    }
};
const isActive = (item) => !!item.url && !isExternal(item.url) && pathOf(item.url) === props.currentPath;
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
    <Teleport to="body">
        <Transition enter-active-class="transition duration-300" enter-from-class="opacity-0" leave-active-class="transition duration-200" leave-to-class="opacity-0">
            <div v-if="open" class="fixed inset-0 z-[80] bg-surface-inverse/50 backdrop-blur-sm lg:hidden" aria-hidden="true" @click="$emit('close')" />
        </Transition>
        <Transition
            enter-active-class="transition duration-500 ease-[cubic-bezier(.16,1,.3,1)]"
            enter-from-class="-translate-x-[110%] opacity-60"
            leave-active-class="transition duration-300 ease-in"
            leave-to-class="-translate-x-[110%] opacity-60"
        >
            <aside
                v-if="open"
                id="mobile-navigation-dialog"
                ref="drawer"
                class="fixed bottom-3 left-3 top-3 z-[90] flex w-[min(86vw,22rem)] flex-col overflow-hidden rounded-3xl border border-line bg-surface-card shadow-card-hover lg:hidden"
                role="dialog"
                aria-modal="true"
                aria-labelledby="mobile-navigation-title"
            >
                <div class="ev-dark-section relative overflow-hidden px-5 pb-5 pt-4 text-content-inverse">
                    <div class="ev-grid-bg pointer-events-none absolute inset-0 opacity-50" aria-hidden="true" />
                    <div class="relative flex items-center justify-between gap-3">
                        <h2 id="mobile-navigation-title" class="flex min-w-0 items-center gap-2 font-display text-lg font-bold">
                            <img v-if="site.logo_url" :src="site.logo_url" alt="" class="h-9 w-auto max-w-28 rounded-lg bg-surface-card object-contain p-1">
                            <span class="truncate">{{ site.name || 'Menu' }}</span>
                        </h2>
                        <button ref="closeButton" type="button" class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-content-inverse/10 transition hover:bg-brand hover:text-brand-contrast" aria-label="Đóng menu" @click="$emit('close')">
                            <StorefrontIcon name="close" class="h-5 w-5" />
                        </button>
                    </div>
                    <p v-if="site.tagline" class="relative mt-2 text-xs text-content-inverse/70">{{ site.tagline }}</p>
                    <div class="relative mt-4 text-content-primary">
                        <StorefrontSearch id="mobile-storefront-search" :model-value="searchKeyword" compact @update:model-value="$emit('update:searchKeyword', $event)" @submit="$emit('search', $event)" />
                    </div>
                </div>

                <nav aria-label="Điều hướng mobile" class="min-h-0 flex-1 overflow-y-auto overscroll-contain p-3">
                    <ul class="space-y-1">
                        <li v-for="(item, index) in items" :key="itemKey(item)" class="ev-drawer-item rounded-xl" :style="{ animationDelay: `${120 + index * 45}ms` }">
                            <div class="flex items-center rounded-xl" :class="isActive(item) && 'bg-brand-soft'">
                                <component
                                    :is="linkComponent(item.url)"
                                    v-if="item.url"
                                    :href="item.url"
                                    :target="isExternal(item.url) ? '_blank' : undefined"
                                    :rel="isExternal(item.url) ? 'noopener noreferrer' : undefined"
                                    class="flex min-h-12 flex-1 items-center gap-3 rounded-xl px-3 font-semibold text-content-primary hover:bg-surface-muted"
                                    :aria-current="isActive(item) ? 'page' : undefined"
                                    @click="$emit('close')"
                                >
                                    <span class="h-1.5 w-1.5 rounded-full" :class="isActive(item) ? 'bg-brand' : 'bg-line-strong'" aria-hidden="true" />
                                    {{ item.label }}
                                </component>
                                <span v-else class="flex min-h-12 flex-1 items-center px-3 font-semibold">{{ item.label }}</span>
                                <button
                                    v-if="item.children?.length"
                                    type="button"
                                    class="grid min-h-12 min-w-12 place-items-center rounded-xl hover:bg-surface-muted"
                                    :aria-label="`Mở menu ${item.label}`"
                                    :aria-expanded="expanded.has(itemKey(item))"
                                    :aria-controls="`mobile-menu-${itemKey(item)}`"
                                    @click="toggle(item)"
                                >
                                    <svg class="h-5 w-5 transition" :class="expanded.has(itemKey(item)) && 'rotate-180'" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 9 6 6 6-6" /></svg>
                                </button>
                            </div>
                            <ul v-if="item.children?.length && expanded.has(itemKey(item))" :id="`mobile-menu-${itemKey(item)}`" class="ml-5 border-l-2 border-brand-border pl-3">
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

                <div class="space-y-2 border-t border-line p-3">
                    <a v-if="contactPhone" :href="`tel:${contactPhone}`" class="ev-shine flex min-h-12 items-center justify-center gap-2 rounded-2xl bg-brand font-display text-sm font-bold text-brand-contrast">
                        <StorefrontIcon name="phone" class="h-4 w-4" />Gọi tư vấn {{ contactPhone }}
                    </a>
                    <Link v-if="accountHref" :href="accountHref" class="flex min-h-11 items-center justify-center gap-2 rounded-2xl border border-line text-sm font-semibold text-content-secondary hover:bg-surface-muted" @click="$emit('close')">
                        <StorefrontIcon name="user" class="h-4 w-4" />{{ accountLabel }}
                    </Link>
                </div>
            </aside>
        </Transition>
    </Teleport>
</template>
