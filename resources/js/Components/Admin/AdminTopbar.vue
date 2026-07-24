<script setup>
import { ref } from 'vue';
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
    <header class="flex min-h-16 items-center justify-between gap-4 border-b border-admin-border bg-admin-page/95 px-4 sm:px-6">
        <div class="flex min-w-0 items-center gap-3">
            <button ref="menuButton" type="button" class="rounded-lg p-2 text-admin-content-muted hover:bg-admin-surface-muted hover:text-admin-content focus:outline-none focus:ring-2 focus:ring-admin-focus lg:hidden" aria-label="Mở menu quản trị" @click="emit('menu')">
                <AdminIcon name="menu" />
            </button>
            <div class="min-w-0">
                <p class="truncate text-sm font-semibold text-admin-content">{{ site.name || 'Quản trị' }}</p>
                <p class="hidden text-xs text-admin-content-muted sm:block">Khu vực quản trị</p>
            </div>
        </div>
        <Dropdown align="right" width="48">
            <template #trigger>
                <button type="button" class="flex max-w-52 items-center gap-2 rounded-lg px-2 py-2 text-left text-sm text-admin-content hover:bg-admin-surface-muted focus:outline-none focus:ring-2 focus:ring-admin-focus" aria-label="Mở menu tài khoản">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-admin-accent/15 text-admin-accent"><AdminIcon name="user" size="16" /></span>
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
