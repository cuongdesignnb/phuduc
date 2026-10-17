<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import StorefrontIcon from './StorefrontIcon.vue';
import { useMobileMenu } from '@/Composables/useMobileMenu';

const props = defineProps({
    site: { type: Object, default: () => ({}) },
    cartCount: { type: Number, default: 0 },
    currentUrl: { type: String, default: '/' },
});
const menu = useMobileMenu();
const currentPath = computed(() => (props.currentUrl || '/').split('?')[0].replace(/\/$/, '') || '/');
const contactPhone = computed(() => props.site.hotline || props.site.phone || '');
const isActive = (path) => (path === '/' ? currentPath.value === '/' : currentPath.value === path || currentPath.value.startsWith(`${path}/`));
const tabs = computed(() => [
    { key: 'home', label: 'Trang chủ', icon: 'home', href: route('home'), active: isActive('/') },
    { key: 'products', label: 'Sản phẩm', icon: 'grid', href: route('products.index'), active: isActive('/san-pham') },
]);
const trailingTabs = computed(() => [
    { key: 'cart', label: 'Giỏ hàng', icon: 'cart', href: route('cart.index'), active: isActive('/gio-hang'), badge: props.cartCount },
]);
</script>

<template>
    <nav aria-label="Thanh điều hướng nhanh" class="pb-safe fixed inset-x-0 bottom-0 z-[60] border-t border-line bg-surface-card/95 shadow-[0_-8px_24px_rgb(15_23_42/0.08)] backdrop-blur-lg lg:hidden">
        <ul class="mx-auto grid h-16 max-w-lg grid-cols-5 items-stretch px-1">
            <li v-for="tab in tabs" :key="tab.key">
                <Link :href="tab.href" class="relative flex h-full flex-col items-center justify-center gap-1 text-[0.6875rem] font-semibold transition" :class="tab.active ? 'text-content-primary' : 'text-content-muted'" :aria-current="tab.active ? 'page' : undefined">
                    <span v-if="tab.active" class="absolute top-0 h-1 w-8 rounded-b-full bg-brand" aria-hidden="true" />
                    <StorefrontIcon :name="tab.icon" class="h-[1.4rem] w-[1.4rem] transition" :class="tab.active && 'scale-110 text-brand-text'" />
                    {{ tab.label }}
                </Link>
            </li>

            <li class="relative flex justify-center">
                <component
                    :is="contactPhone ? 'a' : Link"
                    :href="contactPhone ? `tel:${contactPhone}` : route('products.index')"
                    class="absolute -top-6 flex flex-col items-center gap-1 text-[0.6875rem] font-bold text-content-primary"
                    :aria-label="contactPhone ? `Gọi tư vấn ${contactPhone}` : 'Xem sản phẩm'"
                >
                    <span class="ev-pulse-dot relative grid h-14 w-14 place-items-center rounded-full bg-brand text-brand-contrast shadow-card-hover ring-4 ring-surface-card">
                        <StorefrontIcon :name="contactPhone ? 'phone' : 'bolt'" class="relative z-10 h-6 w-6" />
                    </span>
                    Tư vấn
                </component>
            </li>

            <li v-for="tab in trailingTabs" :key="tab.key">
                <Link :href="tab.href" class="relative flex h-full flex-col items-center justify-center gap-1 text-[0.6875rem] font-semibold transition" :class="tab.active ? 'text-content-primary' : 'text-content-muted'" :aria-current="tab.active ? 'page' : undefined" :aria-label="`${tab.label}, ${tab.badge} sản phẩm`">
                    <span v-if="tab.active" class="absolute top-0 h-1 w-8 rounded-b-full bg-brand" aria-hidden="true" />
                    <span class="relative">
                        <StorefrontIcon :name="tab.icon" class="h-[1.4rem] w-[1.4rem]" :class="tab.active && 'text-brand-text'" />
                        <span v-if="tab.badge" class="absolute -right-2.5 -top-1.5 grid min-h-4 min-w-4 place-items-center rounded-full bg-danger px-1 text-[0.625rem] font-bold leading-none text-content-inverse">{{ tab.badge }}</span>
                    </span>
                    {{ tab.label }}
                </Link>
            </li>

            <li>
                <button
                    type="button"
                    class="flex h-full w-full flex-col items-center justify-center gap-1 text-[0.6875rem] font-semibold transition"
                    :class="menu.open.value ? 'text-content-primary' : 'text-content-muted'"
                    aria-controls="mobile-navigation-dialog"
                    :aria-expanded="menu.open.value"
                    @click="menu.show($event.currentTarget)"
                >
                    <StorefrontIcon name="menu" class="h-[1.4rem] w-[1.4rem]" />
                    Menu
                </button>
            </li>
        </ul>
    </nav>
</template>
