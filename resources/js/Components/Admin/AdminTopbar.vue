<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import AdminIcon from './AdminIcon.vue';

defineProps({
    site: { type: Object, default: () => ({}) },
    user: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['menu']);
const menuButton = ref(null);
defineExpose({
    focusMenuButton: () => menuButton.value?.focus(),
});
</script>

<template>
    <header class="flex min-h-16 items-center justify-between gap-4 border-b border-white/10 bg-carbon-950/95 px-4 sm:px-6">
        <div class="flex min-w-0 items-center gap-3">
            <button ref="menuButton" type="button" class="rounded-lg p-2 text-carbon-300 hover:bg-white/10 hover:text-white focus:outline-none focus:ring-2 focus:ring-volt-400 lg:hidden" aria-label="Mở menu quản trị" @click="emit('menu')">
                <AdminIcon name="menu" />
            </button>
            <div class="min-w-0">
                <p class="truncate text-sm font-semibold text-white">{{ site.name || 'Quản trị' }}</p>
                <p class="hidden text-xs text-carbon-500 sm:block">Khu vực quản trị</p>
            </div>
        </div>
        <Dropdown align="right" width="48">
            <template #trigger>
                <button type="button" class="flex max-w-52 items-center gap-2 rounded-lg px-2 py-2 text-left text-sm text-carbon-200 hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-volt-400" aria-label="Mở menu tài khoản">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-volt-500/15 text-volt-300"><AdminIcon name="user" size="16" /></span>
                    <span class="hidden truncate sm:block">{{ user.name || 'Tài khoản' }}</span>
                </button>
            </template>
            <template #content>
                <DropdownLink :href="route('profile.edit')">Hồ sơ</DropdownLink>
                <DropdownLink :href="route('logout')" method="post" as="button">Đăng xuất</DropdownLink>
            </template>
        </Dropdown>
    </header>
</template>
