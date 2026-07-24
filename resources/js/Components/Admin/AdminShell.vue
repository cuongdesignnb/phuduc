<script setup>
import { computed, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AdminMobileNavigation from './AdminMobileNavigation.vue';
import AdminSidebar from './AdminSidebar.vue';
import AdminTopbar from './AdminTopbar.vue';

const page = usePage();
const sidebarCollapsed = ref(false);
const mobileOpen = ref(false);
const topbar = ref(null);
const site = computed(() => page.props.site || {});
const user = computed(() => page.props.auth?.user || {});
const navigation = computed(() => page.props.admin?.navigation || []);

const closeMobile = () => {
    mobileOpen.value = false;
    topbar.value?.focusMenuButton();
};
</script>

<template>
    <div class="min-h-screen bg-carbon-950 text-white">
        <a href="#admin-main" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[60] focus:rounded-lg focus:bg-volt-400 focus:px-4 focus:py-2 focus:text-carbon-950">Bỏ qua đến nội dung chính</a>
        <div class="hidden lg:fixed lg:inset-y-0 lg:left-0 lg:z-40 lg:block" :class="sidebarCollapsed ? 'lg:w-[4.5rem]' : 'lg:w-64'">
            <AdminSidebar :navigation="navigation" :site="site" :collapsed="sidebarCollapsed" />
        </div>
        <AdminMobileNavigation :open="mobileOpen" :navigation="navigation" :site="site" @close="closeMobile" />
        <div :class="sidebarCollapsed ? 'lg:pl-[4.5rem]' : 'lg:pl-64'" class="min-h-screen transition-[padding] duration-200">
            <AdminTopbar ref="topbar" :site="site" :user="user" @menu="mobileOpen = true" />
            <div v-if="$slots.header" class="border-b border-white/10 bg-carbon-950 px-4 py-5 sm:px-6">
                <div class="mx-auto max-w-7xl"><slot name="header" /></div>
            </div>
            <main id="admin-main" tabindex="-1" class="mx-auto max-w-7xl px-4 py-6 sm:px-6">
                <slot />
            </main>
        </div>
        <button type="button" class="fixed bottom-4 right-4 z-30 hidden rounded-lg border border-white/10 bg-carbon-900 p-2 text-carbon-300 shadow-lg hover:text-white focus:outline-none focus:ring-2 focus:ring-volt-400 lg:block" :aria-label="sidebarCollapsed ? 'Mở rộng menu quản trị' : 'Thu gọn menu quản trị'" @click="sidebarCollapsed = !sidebarCollapsed">
            <span aria-hidden="true">{{ sidebarCollapsed ? '→' : '←' }}</span>
        </button>
    </div>
</template>
