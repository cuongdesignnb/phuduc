<script setup>
import { Link, router } from '@inertiajs/vue3';
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import DesktopNavigation from './DesktopNavigation.vue';
import MobileNavigation from './MobileNavigation.vue';
import StorefrontContainer from './StorefrontContainer.vue';
import StorefrontIcon from './StorefrontIcon.vue';
import StorefrontSearch from './StorefrontSearch.vue';
import { useMobileMenu } from '@/Composables/useMobileMenu';

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
const searchOpen = ref(false);
const searchPanel = ref(null);
const menu = useMobileMenu();
const scrolled = ref(false);
const contactPhone = computed(() => props.site.hotline || props.site.phone || '');
const accountHref = computed(() => {
    if (!props.authUser) return route('login');
    return props.authUser.is_admin ? route('dashboard') : route('profile.edit');
});
const accountLabel = computed(() => props.authUser?.name || 'Đăng nhập');
const submitSearch = (keyword) => {
    const search = keyword.trim();
    if (!search) return;
    menu.hide();
    searchOpen.value = false;
    router.get(route('products.index'), { search });
};
const toggleSearch = async () => {
    searchOpen.value = !searchOpen.value;
    if (searchOpen.value) {
        await nextTick();
        searchPanel.value?.querySelector('input')?.focus();
    }
};
const onScroll = () => { scrolled.value = window.scrollY > 12; };
const onKeydown = (event) => { if (event.key === 'Escape') searchOpen.value = false; };
onMounted(() => {
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
    document.addEventListener('keydown', onKeydown);
});
onBeforeUnmount(() => {
    window.removeEventListener('scroll', onScroll);
    document.removeEventListener('keydown', onKeydown);
});
watch(menu.open, async (open, wasOpen) => {
    if (!open && wasOpen && menu.trigger.value) {
        await nextTick();
        menu.trigger.value.focus();
    }
});
watch(() => props.currentUrl, (url) => {
    if ((url || '').startsWith('/san-pham')) searchKeyword.value = initialSearch(url);
    menu.hide();
    searchOpen.value = false;
});
</script>

<template>
    <header class="sticky top-0 z-50 border-b bg-surface-card/95 backdrop-blur-lg transition-shadow duration-300" :class="scrolled ? 'border-line shadow-card' : 'border-transparent'">
        <StorefrontContainer>
            <div class="flex items-center gap-3 transition-[min-height] duration-300 lg:gap-6" :class="scrolled ? 'min-h-14 lg:min-h-16' : 'min-h-16 lg:min-h-[4.5rem]'">
                <Link :href="route('home')" class="group flex min-w-0 shrink-0 items-center gap-2.5">
                    <span v-if="!site.logo_url" class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-brand text-brand-contrast transition group-hover:rotate-6" aria-hidden="true">
                        <StorefrontIcon name="bolt" class="h-5 w-5" />
                    </span>
                    <img v-if="site.logo_url" :src="site.logo_url" :alt="site.name || 'Trang chủ'" width="160" height="48" class="h-10 w-auto max-w-36 object-contain sm:h-11 sm:max-w-44">
                    <span class="min-w-0" :class="site.logo_url && 'hidden sm:block lg:hidden xl:block'">
                        <strong class="block truncate py-0.5 font-display text-lg font-extrabold uppercase leading-snug tracking-tight text-content-primary sm:text-xl">{{ site.name }}</strong>
                        <small v-if="site.tagline" class="mt-1 hidden max-w-48 truncate text-[10px] font-semibold uppercase tracking-[0.14em] text-content-muted xl:block">{{ site.tagline }}</small>
                    </span>
                </Link>

                <div class="hidden h-full min-w-0 flex-1 justify-center lg:flex">
                    <DesktopNavigation :items="items" :current-path="currentPath" />
                </div>

                <div class="ml-auto flex shrink-0 items-center gap-1 sm:gap-2 lg:ml-0">
                    <button
                        type="button"
                        class="grid h-11 w-11 place-items-center rounded-full text-content-secondary transition hover:bg-surface-muted hover:text-content-primary"
                        :class="searchOpen && 'bg-brand-soft text-content-primary'"
                        aria-label="Tìm kiếm sản phẩm"
                        aria-controls="header-search-panel"
                        :aria-expanded="searchOpen"
                        @click="toggleSearch"
                    >
                        <StorefrontIcon :name="searchOpen ? 'close' : 'search'" class="h-5 w-5" />
                    </button>
                    <Link :href="accountHref" class="hidden h-11 w-11 place-items-center rounded-full text-content-secondary transition hover:bg-surface-muted hover:text-content-primary lg:grid" :aria-label="`Tài khoản: ${accountLabel}`">
                        <StorefrontIcon name="user" class="h-5 w-5" />
                    </Link>
                    <Link :href="route('cart.index')" class="relative hidden h-11 w-11 place-items-center rounded-full text-content-secondary transition hover:bg-surface-muted hover:text-content-primary sm:grid" :aria-label="`Giỏ hàng, ${cartCount} sản phẩm`">
                        <StorefrontIcon name="cart" class="h-5 w-5" />
                        <span v-if="cartCount" class="absolute right-0.5 top-0.5 grid min-h-[1.125rem] min-w-[1.125rem] place-items-center rounded-full bg-brand px-1 text-[10px] font-black text-brand-contrast">{{ cartCount }}</span>
                    </Link>
                    <a
                        v-if="contactPhone"
                        :href="`tel:${contactPhone}`"
                        class="ev-shine group hidden min-h-11 items-center gap-2 rounded-full bg-brand px-5 font-display text-sm font-bold text-brand-contrast shadow-ds-sm transition hover:-translate-y-0.5 hover:bg-brand-hover md:inline-flex"
                    >
                        Nhận tư vấn
                        <StorefrontIcon name="arrow" class="h-4 w-4 transition group-hover:translate-x-0.5" />
                    </a>
                </div>
            </div>
        </StorefrontContainer>

        <Transition enter-active-class="transition duration-300 ease-out" enter-from-class="-translate-y-2 opacity-0" leave-active-class="transition duration-150" leave-to-class="-translate-y-2 opacity-0">
            <div v-show="searchOpen" id="header-search-panel" ref="searchPanel" class="absolute inset-x-0 top-full border-b border-line bg-surface-card shadow-card">
                <StorefrontContainer size="content" class="py-4">
                    <StorefrontSearch id="desktop-storefront-search" v-model="searchKeyword" @submit="submitSearch" />
                </StorefrontContainer>
            </div>
        </Transition>

        <MobileNavigation
            v-model:search-keyword="searchKeyword"
            :open="menu.open.value"
            :items="items"
            :site="site"
            :current-path="currentPath"
            :account-href="accountHref"
            :account-label="accountLabel"
            @close="menu.hide()"
            @search="submitSearch"
        />
    </header>
</template>
