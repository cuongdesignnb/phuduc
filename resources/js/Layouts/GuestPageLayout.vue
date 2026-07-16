<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const page = usePage();
const mobileMenuOpen = ref(false);
const flashMessage = ref(null);
const flashType = ref('success');

const labels = {
    allCategories: 'T\u1ea5t c\u1ea3 danh m\u1ee5c',
    searchPlaceholder: 'B\u1ea1n c\u1ea7n t\u00ecm s\u1ea3n ph\u1ea9m n\u00e0o?',
    categoryMenu: 'Danh m\u1ee5c s\u1ea3n ph\u1ea9m',
    hotline: 'Hotline t\u01b0 v\u1ea5n',
    freeCall: '(Mi\u1ec5n ph\u00ed cu\u1ed9c g\u1ecdi)',
    account: 'T\u00e0i kho\u1ea3n',
    login: '\u0110\u0103ng nh\u1eadp / \u0110\u0103ng k\u00fd',
    cart: 'Gi\u1ecf h\u00e0ng',
    mobileSearch: 'T\u00ecm s\u1ea3n ph\u1ea9m',
    contact: 'Th\u00f4ng tin li\u00ean h\u1ec7',
    showroom: 'Showroom & h\u1ec7 th\u1ed1ng',
    policy: 'Ch\u00ednh s\u00e1ch',
    newsletter: '\u0110\u0103ng k\u00fd nh\u1eadn tin',
    newsletterCopy: 'Nh\u1eadn th\u00f4ng tin s\u1ea3n ph\u1ea9m m\u1edbi, \u01b0u \u0111\u00e3i v\u00e0 ki\u1ebfn th\u1ee9c h\u1eefu \u00edch.',
    emailPlaceholder: 'Nh\u1eadp email c\u1ee7a b\u1ea1n',
    subscribe: '\u0110\u0103ng k\u00fd',
    warranty: 'Ch\u00ednh s\u00e1ch b\u1ea3o h\u00e0nh',
    orderLookup: 'Tra c\u1ee9u \u0111\u01a1n h\u00e0ng',
    shipping: 'Ch\u00ednh s\u00e1ch v\u1eadn chuy\u1ec3n',
};

const site = computed(() => page.props.site || {});
const contactPhone = computed(() => site.value.hotline || site.value.phone || '');
const copyright = computed(() => site.value.copyright || '');

const fallbackNavLinks = [
    { label: 'Trang ch\u1ee7', href: route('home') },
    { label: 'S\u1ea3n ph\u1ea9m', href: route('products.index'), dropdown: true },
    { label: 'Gi\u1ea3i ph\u00e1p theo ng\u00e0nh', href: route('products.index') },
    { label: 'D\u1ecbch v\u1ee5', href: route('warranty-lookup.index') },
    { label: 'Tin t\u1ee9c', href: route('news.index') },
    { label: 'V\u1ec1 ch\u00fang t\u00f4i', href: route('about') },
    { label: 'Li\u00ean h\u1ec7', href: route('about') },
];

const normalizeNavigationItem = (item) => ({
    ...item,
    href: item.url || '#',
    dropdown: (item.children || []).length > 0,
    children: (item.children || []).map(normalizeNavigationItem),
});

const headerLinks = computed(() => {
    const items = page.props.navigation?.header || [];
    return items.length ? items.map(normalizeNavigationItem) : fallbackNavLinks;
});

const footerGroups = computed(() => {
    return (page.props.navigation?.footer || []).map(normalizeNavigationItem);
});

const currentPath = computed(() => (page.url || '/').split('?')[0]);

const cartCount = computed(() => {
    const cart = page.props.cart || {};
    return Object.values(cart).reduce((sum, item) => sum + (item.quantity || 0), 0);
});

watch(() => page.props.flash, (flash) => {
    if (flash?.success) {
        flashMessage.value = flash.success;
        flashType.value = 'success';
    } else if (flash?.error) {
        flashMessage.value = flash.error;
        flashType.value = 'error';
    }

    if (flashMessage.value) {
        setTimeout(() => { flashMessage.value = null; }, 3600);
    }
}, { immediate: true, deep: true });
</script>

<template>
    <div class="min-h-screen bg-[#f5f6f8] text-[#20242c] antialiased">
        <header class="sticky top-0 z-50 bg-white shadow-[0_2px_18px_rgba(31,41,55,0.08)]">
            <div class="mx-auto max-w-[1780px] px-5 2xl:px-8">
                <div class="flex h-[72px] items-center gap-5">
                    <Link :href="route('home')" class="flex min-w-[310px] shrink-0 items-center gap-3">
                        <img v-if="site.logo_url" :src="site.logo_url" :alt="site.name" class="h-[48px] w-auto max-w-[160px] object-contain" />
                        <div class="min-w-0">
                            <div class="truncate text-[25px] font-black uppercase leading-none tracking-tight text-[#747b86]">{{ site.name }}</div>
                            <div v-if="site.tagline" class="mt-1 max-w-[250px] text-[10px] font-extrabold uppercase leading-tight tracking-[.12em] text-[#6f7680]">{{ site.tagline }}</div>
                        </div>
                    </Link>

                    <div class="hidden min-w-0 flex-1 items-center xl:flex">
                        <div class="flex h-[44px] w-full max-w-[620px] overflow-hidden rounded-[9px] border border-[#dfe3e8] bg-white shadow-sm">
                            <button class="flex min-w-[164px] items-center justify-between border-r border-[#e5e8ed] px-4 text-sm font-bold text-[#333943]">
                                {{ labels.allCategories }}
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 9 6 6 6-6"/></svg>
                            </button>
                            <input class="h-full flex-1 border-0 px-4 text-sm text-[#59616e] placeholder:text-[#a2a9b3] focus:ring-0" :placeholder="labels.searchPlaceholder" />
                            <button class="grid w-[58px] place-items-center bg-[#ffd400] text-[#111827] transition hover:bg-[#f3c800]">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z"/></svg>
                            </button>
                        </div>
                    </div>

                    <div class="ml-auto hidden shrink-0 items-center gap-5 xl:flex">
                        <a :href="contactPhone ? `tel:${contactPhone}` : '#'" class="flex shrink-0 items-center gap-2.5 whitespace-nowrap">
                            <span class="grid h-10 w-10 place-items-center rounded-full border border-[#e1e4e8] bg-white">
                                <svg class="h-6 w-6 text-[#80652a]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106a1.125 1.125 0 0 0-1.173.417l-.97 1.293a1.125 1.125 0 0 1-1.21.38 12.035 12.035 0 0 1-7.143-7.143 1.125 1.125 0 0 1 .38-1.21l1.293-.97c.356-.267.52-.723.417-1.173L6.963 3.102A1.125 1.125 0 0 0 5.872 2.25H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/></svg>
                            </span>
                            <span class="leading-tight">
                                <span class="block text-xs text-[#7b828e]">{{ labels.hotline }}</span>
                                <span class="block text-[17px] font-black leading-tight text-[#ff9d00]">{{ contactPhone || 'Liên hệ' }}</span>
                                <span class="block text-xs text-[#7b828e]">{{ labels.freeCall }}</span>
                            </span>
                        </a>
                        <Link :href="$page.props.auth?.user ? route('dashboard') : route('login')" class="flex shrink-0 items-center gap-2.5 whitespace-nowrap">
                            <span class="grid h-10 w-10 place-items-center rounded-full bg-[#f4f5f7]">
                                <svg class="h-6 w-6 text-[#5b626c]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15.75 7.5a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.25a7.5 7.5 0 0 1 15 0"/></svg>
                            </span>
                            <span class="text-sm font-bold leading-tight text-[#343a44]">{{ labels.account }}<br><span class="font-medium text-[#747b86]">{{ labels.login }}</span></span>
                        </Link>
                        <Link :href="route('cart.index')" class="relative flex shrink-0 items-center gap-2.5 whitespace-nowrap">
                            <span class="grid h-10 w-10 place-items-center rounded-full bg-[#fff8d9]">
                                <svg class="h-6 w-6 text-[#967513]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437m0 0L6.75 14.25h10.5l3-8.978H5.106ZM8.25 20.25h.008v.008H8.25v-.008Zm9 0h.008v.008h-.008v-.008Z"/></svg>
                            </span>
                            <span class="text-sm font-black">{{ labels.cart }}</span>
                            <span v-if="cartCount > 0" class="absolute -right-4 -top-2 grid h-6 min-w-6 place-items-center rounded-full bg-[#ffd400] px-1 text-xs font-black">{{ cartCount }}</span>
                        </Link>
                    </div>

                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="ml-auto grid h-11 w-11 place-items-center rounded-lg bg-[#f0f2f5] xl:hidden">
                        <svg v-if="!mobileMenuOpen" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16M4 12h16M4 17h16"/></svg>
                        <svg v-else class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="hidden h-[50px] items-center gap-8 border-t border-[#eef0f3] xl:flex">
                    <button class="flex h-10 items-center gap-3 rounded-[10px] bg-[#eef0f3] px-5 text-[15px] font-extrabold uppercase text-[#232832]">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        {{ labels.categoryMenu }}
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 9 6 6 6-6"/></svg>
                    </button>
                    <nav class="flex h-full items-center gap-8">
                        <Link v-for="item in headerLinks" :key="item.label" :href="item.href" class="relative flex h-full items-center text-sm font-bold text-[#303640] transition hover:text-[#e4a900]" :class="{ 'text-[#f1b900] after:absolute after:bottom-0 after:left-0 after:h-[3px] after:w-full after:rounded-full after:bg-[#ffd400]': currentPath === item.href }">
                            {{ item.label }}
                            <svg v-if="item.dropdown" class="ml-1 h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 9 6 6 6-6"/></svg>
                        </Link>
                    </nav>
                </div>
            </div>

            <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 -translate-y-2" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-2">
                <div v-if="mobileMenuOpen" class="border-t border-[#e7e9ee] bg-white px-5 py-4 xl:hidden">
                    <div class="mb-4 flex h-11 overflow-hidden rounded-lg border border-[#dfe3e8]">
                        <input class="h-full flex-1 border-0 px-3 text-sm focus:ring-0" :placeholder="labels.mobileSearch" />
                        <button class="grid w-12 place-items-center bg-[#ffd400]"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z"/></svg></button>
                    </div>
                    <Link v-for="item in headerLinks" :key="item.label" :href="item.href" @click="mobileMenuOpen = false" class="block rounded-lg px-3 py-3 text-sm font-bold text-[#343a44] hover:bg-[#f5f6f8]">
                        {{ item.label }}
                    </Link>
                </div>
            </Transition>
        </header>

        <main>
            <Transition enter-active-class="transition-all duration-300 ease-out" enter-from-class="opacity-0 -translate-y-3" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition-all duration-200 ease-in" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-3">
                <div v-if="flashMessage" class="fixed right-5 top-24 z-[70] max-w-sm rounded-xl border bg-white px-5 py-4 shadow-xl" :class="flashType === 'success' ? 'border-emerald-200 text-emerald-700' : 'border-red-200 text-red-700'">
                    <div class="text-sm font-semibold">{{ flashMessage }}</div>
                </div>
            </Transition>
            <slot />
        </main>

        <footer class="border-t border-[#dfe3e8] bg-[#eef1f5]">
            <div class="mx-auto grid max-w-[2040px] gap-8 px-5 py-8 lg:grid-cols-[1.3fr_1fr_1.2fr_1fr_1.4fr] 2xl:px-20">
                <div>
                    <Link :href="route('home')" class="mb-4 flex items-center gap-3">
                        <img v-if="site.logo_url" :src="site.logo_url" :alt="site.name" class="h-12 w-auto max-w-[160px] object-contain" />
                        <div class="text-[24px] font-black uppercase text-[#747b86]">{{ site.name }}</div>
                    </Link>
                    <p v-if="site.description" class="max-w-sm text-sm leading-6 text-[#59616e]">{{ site.description }}</p>
                </div>
                <div>
                    <h3 class="mb-4 text-base font-black uppercase">{{ labels.contact }}</h3>
                    <div class="space-y-2 text-sm leading-6 text-[#59616e]">
                        <p v-if="contactPhone">Hotline: {{ contactPhone }}</p>
                        <p v-if="site.email">Email: {{ site.email }}</p>
                        <p v-if="site.address">{{ site.address }}</p>
                        <p v-if="site.working_hours">{{ site.working_hours }}</p>
                    </div>
                </div>
                <div v-if="!footerGroups.length">
                    <h3 class="mb-4 text-base font-black uppercase">{{ labels.showroom }}</h3>
                    <div class="space-y-2 text-sm leading-6 text-[#59616e]">
                        <p v-if="site.address">{{ site.address }}</p>
                        <p v-if="site.working_hours">{{ site.working_hours }}</p>
                        <p v-if="!site.address && !site.working_hours">Thông tin showroom đang được cập nhật.</p>
                    </div>
                </div>
                <template v-else>
                    <div v-for="group in footerGroups.slice(0, 2)" :key="group.label">
                        <h3 class="mb-4 text-base font-black uppercase">{{ group.label }}</h3>
                        <div class="space-y-2 text-sm leading-6 text-[#59616e]">
                            <Link v-for="child in group.children" :key="child.label" :href="child.href" class="block hover:text-[#d49d00]">{{ child.label }}</Link>
                        </div>
                    </div>
                </template>
                <div v-if="!footerGroups.length">
                    <h3 class="mb-4 text-base font-black uppercase">{{ labels.policy }}</h3>
                    <div class="space-y-2 text-sm leading-6 text-[#59616e]">
                        <Link :href="route('warranty-lookup.index')" class="block hover:text-[#d49d00]">{{ labels.warranty }}</Link>
                        <Link :href="route('order-lookup.index')" class="block hover:text-[#d49d00]">{{ labels.orderLookup }}</Link>
                        <Link :href="route('products.index')" class="block hover:text-[#d49d00]">{{ labels.shipping }}</Link>
                    </div>
                </div>
                <div>
                    <h3 class="mb-4 text-base font-black uppercase">{{ labels.newsletter }}</h3>
                    <p class="mb-4 text-sm leading-6 text-[#59616e]">{{ labels.newsletterCopy }}</p>
                    <div class="flex overflow-hidden rounded-lg border border-[#d9dde4] bg-white">
                        <input class="min-w-0 flex-1 border-0 px-3 text-sm focus:ring-0" :placeholder="labels.emailPlaceholder" />
                        <button class="bg-[#ffd400] px-5 text-sm font-black">{{ labels.subscribe }}</button>
                    </div>
                </div>
            </div>
            <div class="border-t border-[#d8dce4] py-4 text-center text-xs text-[#7b828e]">{{ copyright }}</div>
        </footer>
    </div>
</template>
