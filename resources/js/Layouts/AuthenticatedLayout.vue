<script setup>
import { ref } from 'vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { Link, usePage } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
const sidebarOpen = ref(true);

const page = usePage();

const adminLinks = [
    { label: 'Tổng quan', route: 'dashboard', icon: 'dashboard' },
    { label: 'Sản phẩm', route: 'admin.products.index', icon: 'cube' },
    { label: 'Đơn hàng', route: 'admin.orders.index', icon: 'cart' },
    { label: 'Menu', route: 'admin.menus.index', icon: 'menu' },
    { label: 'N\u1ed9i dung trang ch\u1ee7', route: 'admin.home-content.index', icon: 'homeContent' },
    { label: 'Bài viết', route: 'admin.posts.index', icon: 'document' },
    { label: 'Danh mục tin', route: 'admin.post-categories.index', icon: 'folder' },
    { label: 'Đánh giá', route: 'admin.reviews.index', icon: 'star' },
    { label: 'Bảo hành', route: 'admin.warranties.index', icon: 'shield' },
    { label: 'Cài đặt', route: 'admin.settings.index', icon: 'cog' },
];

const icons = {
    dashboard: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
    cube: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
    cart: 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z',
    menu: 'M4 6h16M4 12h16M4 18h7',
    homeContent: 'M3 10.5 12 3l9 7.5M5.25 9.75V21h13.5V9.75M9 21v-6h6v6M4.5 13.5h15',
    document: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
    folder: 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z',
    star: 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z',
    shield: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
    cog: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
};

const isActive = (linkRoute) => {
    return route().current(linkRoute) || route().current(linkRoute.replace('.index', '.*'));
};
</script>

<template>
    <div>
        <div class="min-h-screen bg-carbon-950 flex">
            <!-- Sidebar -->
            <aside
                :class="sidebarOpen ? 'w-64' : 'w-[72px]'"
                class="fixed inset-y-0 left-0 z-30 bg-carbon-900/80 backdrop-blur-xl border-r border-white/5 transition-all duration-300 ease-in-out flex flex-col"
            >
                <!-- Logo -->
                <div class="flex items-center h-16 px-4 border-b border-white/5">
                    <Link :href="route('dashboard')" class="flex items-center gap-3 min-w-0">
                        <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-volt-500 to-volt-600 flex items-center justify-center shrink-0 shadow-lg shadow-volt-500/20">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <span v-if="sidebarOpen" class="text-base font-display font-bold text-white truncate tracking-wide">PHU DUC EV</span>
                    </Link>
                    <button @click="sidebarOpen = !sidebarOpen" class="ml-auto text-carbon-400 hover:text-white transition-colors p-1 rounded-lg hover:bg-white/5">
                        <svg class="w-5 h-5 transition-transform duration-300" :class="{ 'rotate-180': !sidebarOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                        </svg>
                    </button>
                </div>

                <!-- Navigation -->
                <nav class="flex-1 py-4 px-3 space-y-1 overflow-y-auto scrollbar-thin">
                    <Link
                        v-for="link in adminLinks"
                        :key="link.route"
                        :href="route(link.route)"
                        :class="[
                            isActive(link.route)
                                ? 'bg-volt-500/10 text-volt-400 border-volt-500/20 shadow-sm shadow-volt-500/5'
                                : 'text-carbon-400 border-transparent hover:bg-white/5 hover:text-white',
                            'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 border group'
                        ]"
                        :title="!sidebarOpen ? link.label : undefined"
                    >
                        <svg class="w-5 h-5 shrink-0 transition-colors" :class="isActive(link.route) ? 'text-volt-400' : 'text-carbon-500 group-hover:text-carbon-300'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="icons[link.icon]" />
                        </svg>
                        <span v-if="sidebarOpen" class="truncate">{{ link.label }}</span>
                    </Link>
                </nav>

                <!-- User section at bottom -->
                <div v-if="sidebarOpen" class="border-t border-white/5 p-4">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-industrial-500 to-industrial-600 flex items-center justify-center text-white text-xs font-bold uppercase">
                            {{ $page.props.auth.user.name?.charAt(0) }}
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-white truncate">{{ $page.props.auth.user.name }}</p>
                            <p class="text-xs text-carbon-500 truncate">{{ $page.props.auth.user.email }}</p>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Main content -->
            <div :class="sidebarOpen ? 'ml-64' : 'ml-[72px]'" class="flex-1 transition-all duration-300 ease-in-out">
                <!-- Top bar -->
                <nav class="sticky top-0 z-20 h-16 border-b border-white/5 bg-carbon-950/80 backdrop-blur-xl">
                    <div class="flex h-full items-center justify-between px-6">
                        <div class="flex items-center">
                            <slot name="breadcrumb" />
                        </div>

                        <div class="hidden sm:flex items-center gap-4">
                            <Link :href="route('home')" class="inline-flex items-center gap-1.5 text-sm text-carbon-400 hover:text-volt-400 transition-colors" target="_blank">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                                Xem Website
                            </Link>
                            <div class="w-px h-6 bg-white/10"></div>
                            <Dropdown align="right" width="48">
                                <template #trigger>
                                    <button type="button" class="inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-medium text-carbon-300 transition hover:bg-white/5 hover:text-white">
                                        <div class="w-7 h-7 rounded-full bg-gradient-to-br from-industrial-500 to-industrial-600 flex items-center justify-center text-white text-xs font-bold uppercase">
                                            {{ $page.props.auth.user.name?.charAt(0) }}
                                        </div>
                                        {{ $page.props.auth.user.name }}
                                        <svg class="h-4 w-4 text-carbon-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </template>
                                <template #content>
                                    <DropdownLink :href="route('profile.edit')">Hồ sơ</DropdownLink>
                                    <DropdownLink :href="route('logout')" method="post" as="button">Đăng xuất</DropdownLink>
                                </template>
                            </Dropdown>
                        </div>

                        <!-- Mobile menu -->
                        <div class="flex items-center sm:hidden">
                            <button @click="showingNavigationDropdown = !showingNavigationDropdown" class="p-2 text-carbon-400 hover:text-white rounded-lg hover:bg-white/5 transition">
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path :class="{ hidden: showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                    <path :class="{ hidden: !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Mobile dropdown -->
                    <div :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }" class="sm:hidden bg-carbon-900 border-b border-white/5">
                        <div class="space-y-1 p-3">
                            <Link v-for="link in adminLinks" :key="link.route" :href="route(link.route)"
                                :class="[isActive(link.route) ? 'bg-volt-500/10 text-volt-400' : 'text-carbon-400 hover:text-white hover:bg-white/5', 'block px-4 py-2.5 rounded-xl text-sm font-medium transition-colors']">
                                {{ link.label }}
                            </Link>
                        </div>
                        <div class="border-t border-white/5 p-4">
                            <p class="text-sm font-medium text-white">{{ $page.props.auth.user.name }}</p>
                            <p class="text-xs text-carbon-500 mb-3">{{ $page.props.auth.user.email }}</p>
                            <Link :href="route('profile.edit')" class="block text-sm text-carbon-400 hover:text-white py-1">Hồ sơ</Link>
                            <Link :href="route('logout')" method="post" as="button" class="block text-sm text-carbon-400 hover:text-white py-1">Đăng xuất</Link>
                        </div>
                    </div>
                </nav>

                <!-- Page header -->
                <header class="border-b border-white/5 bg-carbon-900/30" v-if="$slots.header">
                    <div class="mx-auto max-w-7xl px-6 py-5">
                        <slot name="header" />
                    </div>
                </header>

                <!-- Page content -->
                <main class="min-h-[calc(100vh-4rem)]">
                    <slot />
                </main>
            </div>
        </div>
    </div>
</template>
