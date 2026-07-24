<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import StorefrontFooter from '@/Components/Storefront/StorefrontFooter.vue';
import StorefrontHeader from '@/Components/Storefront/StorefrontHeader.vue';

const page = usePage();
const site = computed(() => page.props.site || {});
const navigation = computed(() => page.props.navigation || { header: [], footer: [] });
const authUser = computed(() => page.props.auth?.user || null);
const cartCount = computed(() => Number(page.props.cart_count || 0));
</script>

<template>
    <div class="flex min-h-screen flex-col bg-surface-page text-content-primary">
        <a href="#main-content" class="fixed left-4 top-4 z-[100] -translate-y-24 rounded-lg bg-surface-inverse px-4 py-3 font-semibold text-content-inverse transition focus:translate-y-0">
            Bỏ qua tới nội dung chính
        </a>
        <StorefrontHeader
            :site="site"
            :navigation="navigation.header"
            :auth-user="authUser"
            :cart-count="cartCount"
            :current-url="page.url"
        />
        <main id="main-content" tabindex="-1" class="flex-1">
            <slot />
        </main>
        <StorefrontFooter :site="site" :groups="navigation.footer" />
    </div>
</template>
