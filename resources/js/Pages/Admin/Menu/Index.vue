<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({
    menus: Array,
});

const deleteMenu = (id) => {
    if (confirm('Bạn có chắc chắn muốn xóa menu này?')) {
        router.delete(route('admin.menus.destroy', id));
    }
};
</script>

<template>
    <Head title="Quản lý Menu" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-2xl font-display font-bold text-white">Quản lý Menu</h2>
                <Link :href="route('admin.menus.create')" class="inline-flex items-center gap-2 rounded-xl bg-volt-500 px-5 py-2.5 text-sm font-semibold text-white hover:bg-volt-600 transition-all shadow-lg shadow-volt-500/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tạo Menu mới
                </Link>
            </div>
        </template>
        <div class="py-6">
            <div class="mx-auto max-w-7xl px-6">
                <div class="overflow-hidden rounded-2xl border border-white/5 bg-carbon-900/50 backdrop-blur-sm">
                    <table class="min-w-full divide-y divide-white/5">
                        <thead class="bg-carbon-800/50">
                            <tr>
                                <th class="px-6 py-3.5 text-left text-xs font-medium uppercase tracking-wider text-carbon-500">Tên Menu</th>
                                <th class="px-6 py-3.5 text-left text-xs font-medium uppercase tracking-wider text-carbon-500">Vị trí</th>
                                <th class="px-6 py-3.5 text-left text-xs font-medium uppercase tracking-wider text-carbon-500">Số mục</th>
                                <th class="px-6 py-3.5 text-right text-xs font-medium uppercase tracking-wider text-carbon-500">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/[.03]">
                            <tr v-for="menu in menus" :key="menu.id" class="hover:bg-white/[.02] transition-colors">
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-white">{{ $fixText(menu.name) }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-carbon-400">{{ menu.location || '—' }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-carbon-400">{{ menu.all_items_count }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                    <Link :href="route('admin.menus.edit', menu.id)" class="text-industrial-400 hover:text-industrial-300 mr-4 transition-colors">Sửa</Link>
                                    <button @click="deleteMenu(menu.id)" class="text-red-400 hover:text-red-300 transition-colors">Xóa</button>
                                </td>
                            </tr>
                            <tr v-if="!menus.length">
                                <td colspan="4" class="px-6 py-12 text-center text-sm text-carbon-600">Chưa có menu nào. Hãy tạo menu đầu tiên!</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
