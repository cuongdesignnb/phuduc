<script setup>
import { Link, router } from '@inertiajs/vue3';
import { computed, nextTick, ref, watch } from 'vue';
import DesktopNavigation from './DesktopNavigation.vue';
import MobileNavigation from './MobileNavigation.vue';
import StorefrontContainer from './StorefrontContainer.vue';
import StorefrontSearch from './StorefrontSearch.vue';

const props = defineProps({
    site: { type: Object, default: () => ({}) },
    navigation: { type: Array, default: () => [] },
    authUser: { type: Object, default: null },
    cartCount: { type: Number, default: 0 },
    currentUrl: { type: String, default: '/' },
});

const fallbackNavigation = [
    { id: 'home', label: 'Trang chủ', url: '/', children: [] },
    { id: 'products', label: 'Sản phẩm', url: '/san-pham', children: [] },
    { id: 'news', label: 'Tin tức', url: '/tin-tuc', children: [] },
    { id: 'warranty', label: 'Tra cứu bảo hành', url: '/tra-cuu-bao-hanh', children: [] },
    { id: 'about', label: 'Về chúng tôi', url: '/gioi-thieu', children: [] },
];
const items = computed(() => props.navigation.length ? props.navigation : fallbackNavigation);
const currentPath = computed(() => (props.currentUrl || '/').split('?')[0].replace(/\/$/, '') || '/');
const initialSearch = (url) => {
    try {
        return new URL(url, window.location.origin).searchParams.get('search') || '';
    } catch {
        return '';
    }
};
const searchKeyword = ref(initialSearch(props.currentUrl));
const mobileOpen = ref(false);
const mobileMenuTrigger = ref(null);
const contactPhone = computed(() => props.site.hotline || props.site.phone || '');
const accountHref = computed(() => {
    if (!props.authUser) return route('login');
    return props.authUser.is_admin ? route('dashboard') : route('profile.edit');
});
const accountLabel = computed(() => props.authUser?.name || 'Đăng nhập');
const submitSearch = (keyword) => {
    const search = keyword.trim();
    if (!search) return;
    mobileOpen.value = false;
    router.get(route('products.index'), { search });
};
watch(mobileOpen, async (open, wasOpen) => {
    if (!open && wasOpen && mobileMenuTrigger.value) {
        await nextTick();
        mobileMenuTrigger.value.focus();
    }
});
watch(() => props.currentUrl, (url) => {
    if ((url || '').startsWith('/san-pham')) searchKeyword.value = initialSearch(url);
    mobileOpen.value = false;
});
</script>

<template>
    <header class="sticky top-0 z-50 border-b border-line bg-surface-card" style="box-shadow: var(--ds-shadow-sm)">
        <StorefrontContainer>
            <div class="flex min-h-[var(--ds-header-height)] items-center gap-3 py-2 lg:gap-6">
                <Link :href="route('home')" class="flex min-w-0 shrink-0 items-center gap-3">
                    <img v-if="site.logo_url" :src="site.logo_url" :alt="site.name || 'Trang chủ'" width="160" height="48" class="h-11 w-auto max-w-36 object-contain sm:max-w-44">
                    <span class="min-w-0" :class="site.logo_url && 'hidden sm:block'">
                        <strong class="block truncate font-display text-xl font-bold uppercase leading-none text-content-primary sm:text-2xl">{{ site.name }}</strong>
                        <small v-if="site.tagline" class="mt-1 hidden max-w-56 truncate text-[10px] font-semibold uppercase tracking-widest text-content-muted xl:block">{{ site.tagline }}</small>
                    </span>
                </Link>

                <div class="hidden min-w-52 max-w-xl flex-1 lg:block">
                    <StorefrontSearch id="desktop-storefront-search" v-model="searchKeyword" @submit="submitSearch" />
                </div>

                <div class="ml-auto flex shrink-0 items-center gap-1 sm:gap-2">
                    <a v-if="contactPhone" :href="`tel:${contactPhone}`" class="hidden min-h-11 items-center gap-2 rounded-lg px-2 text-sm font-semibold text-content-secondary hover:bg-surface-muted xl:flex">
                        <svg class="h-5 w-5 text-brand-text" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106a1.125 1.125 0 0 0-1.173.417l-.97 1.293" /></svg>
                        <span><small class="block text-[10px] uppercase text-content-muted">Hotline</small>{{ contactPhone }}</span>
                    </a>
                    <Link :href="accountHref" class="grid min-h-11 min-w-11 place-items-center rounded-lg text-content-secondary hover:bg-surface-muted" :aria-label="`Tài khoản: ${accountLabel}`">
                        <svg class="h-6 w-6" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15.75 7.5a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.25a7.5 7.5 0 0 1 15 0" /></svg>
                    </Link>
                    <Link :href="route('cart.index')" class="relative grid min-h-11 min-w-11 place-items-center rounded-lg bg-brand-soft text-content-primary hover:bg-brand-muted" :aria-label="`Giỏ hàng, ${cartCount} sản phẩm`">
                        <svg class="h-6 w-6" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437L6.75 14.25h10.5l3-8.978H5.106M8.25 20.25h.008v.008H8.25v-.008Zm9 0h.008v.008h-.008v-.008Z" /></svg>
                        <span v-if="cartCount" class="absolute right-0 top-0 grid min-h-5 min-w-5 translate-x-1/4 -translate-y-1/4 place-items-center rounded-full bg-brand px-1 text-[10px] font-black text-brand-contrast">{{ cartCount }}</span>
                    </Link>
                    <button ref="mobileMenuTrigger" type="button" class="grid min-h-11 min-w-11 place-items-center rounded-lg bg-surface-muted lg:hidden" aria-label="Mở menu" :aria-expanded="mobileOpen" aria-controls="mobile-navigation-dialog" @click="mobileOpen = true">
                        <svg class="h-6 w-6" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16M4 12h16M4 17h16" /></svg>
                    </button>
                </div>
            </div>

            <div class="hidden h-12 border-t border-line-subtle lg:block">
                <DesktopNavigation :items="items" :current-path="currentPath" />
            </div>
        </StorefrontContainer>

        <MobileNavigation v-model:search-keyword="searchKeyword" :open="mobileOpen" :items="items" @close="mobileOpen = false" @search="submitSearch" />
    </header>
</template>
