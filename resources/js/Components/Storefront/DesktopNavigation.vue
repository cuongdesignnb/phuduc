<script setup>
import { Link } from '@inertiajs/vue3';
import { onBeforeUnmount, onMounted, ref } from 'vue';

const props = defineProps({
    items: { type: Array, default: () => [] },
    currentPath: { type: String, default: '/' },
});

const navigation = ref(null);
const openId = ref(null);
const itemKey = (item) => item.id || item.label;
const isExternal = (url) => /^(https?:)?\/\//.test(url || '');
const linkComponent = (url) => (isExternal(url) ? 'a' : Link);
const pathFromUrl = (url) => {
    if (!url) return '';
    try {
        return new URL(url, window.location.origin).pathname.replace(/\/$/, '') || '/';
    } catch {
        return '';
    }
};
const isActive = (item) => {
    const ownPath = pathFromUrl(item.url);
    const ownActive = ownPath && (props.currentPath === ownPath || (ownPath !== '/' && props.currentPath.startsWith(`${ownPath}/`)));

    return ownActive || (item.children || []).some(isActive);
};
const close = () => { openId.value = null; };
const toggle = (item) => { openId.value = openId.value === itemKey(item) ? null : itemKey(item); };
const onDocumentClick = (event) => {
    if (!navigation.value?.contains(event.target)) close();
};
const onDocumentKeydown = (event) => {
    if (event.key === 'Escape') close();
};
onMounted(() => {
    document.addEventListener('click', onDocumentClick);
    document.addEventListener('keydown', onDocumentKeydown);
});
onBeforeUnmount(() => {
    document.removeEventListener('click', onDocumentClick);
    document.removeEventListener('keydown', onDocumentKeydown);
});
</script>

<template>
    <nav ref="navigation" aria-label="Điều hướng chính" class="h-full">
        <ul class="flex h-full items-stretch gap-1 xl:gap-3">
            <li
                v-for="item in items"
                :key="itemKey(item)"
                class="relative flex items-center"
                @mouseenter="item.children?.length && (openId = itemKey(item))"
                @mouseleave="item.children?.length && close()"
            >
                <component
                    :is="linkComponent(item.url)"
                    v-if="item.url"
                    :href="item.url"
                    :target="isExternal(item.url) ? '_blank' : undefined"
                    :rel="isExternal(item.url) ? 'noopener noreferrer' : undefined"
                    class="flex min-h-11 items-center rounded-lg px-3 text-sm font-bold transition hover:bg-brand-soft hover:text-content-primary"
                    :class="isActive(item) ? 'text-brand-text' : 'text-content-secondary'"
                    @click="close"
                >
                    {{ item.label }}
                </component>
                <span v-else class="flex min-h-11 items-center px-3 text-sm font-bold text-content-secondary">{{ item.label }}</span>
                <button
                    v-if="item.children?.length"
                    type="button"
                    class="grid min-h-11 min-w-9 place-items-center rounded-lg text-content-muted hover:bg-surface-muted hover:text-content-primary"
                    :aria-label="`Mở menu ${item.label}`"
                    aria-haspopup="true"
                    :aria-expanded="openId === itemKey(item)"
                    :aria-controls="`desktop-menu-${itemKey(item)}`"
                    @click.stop="toggle(item)"
                >
                    <svg class="h-4 w-4 transition" :class="openId === itemKey(item) && 'rotate-180'" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 9 6 6 6-6" /></svg>
                </button>

                <div
                    v-if="item.children?.length && openId === itemKey(item)"
                    :id="`desktop-menu-${itemKey(item)}`"
                    class="absolute left-0 top-full z-30 min-w-64 rounded-xl border border-line bg-surface-elevated p-2"
                    style="box-shadow: var(--ds-shadow-dropdown)"
                >
                    <div v-for="child in item.children" :key="itemKey(child)" class="rounded-lg p-1">
                        <component
                            :is="linkComponent(child.url)"
                            v-if="child.url"
                            :href="child.url"
                            :target="isExternal(child.url) ? '_blank' : undefined"
                            :rel="isExternal(child.url) ? 'noopener noreferrer' : undefined"
                            class="flex min-h-10 items-center rounded-lg px-3 text-sm font-semibold text-content-secondary hover:bg-brand-soft hover:text-content-primary"
                            @click="close"
                        >{{ child.label }}</component>
                        <p v-else class="px-3 py-2 text-sm font-bold text-content-primary">{{ child.label }}</p>
                        <div v-if="child.children?.length" class="ml-3 border-l border-line-subtle pl-2">
                            <component
                                :is="linkComponent(grandchild.url)"
                                v-for="grandchild in child.children.filter((entry) => entry.url)"
                                :key="itemKey(grandchild)"
                                :href="grandchild.url"
                                :target="isExternal(grandchild.url) ? '_blank' : undefined"
                                :rel="isExternal(grandchild.url) ? 'noopener noreferrer' : undefined"
                                class="block rounded-md px-3 py-2 text-sm text-content-secondary hover:bg-surface-muted hover:text-content-primary"
                                @click="close"
                            >{{ grandchild.label }}</component>
                        </div>
                    </div>
                </div>
            </li>
        </ul>
    </nav>
</template>
