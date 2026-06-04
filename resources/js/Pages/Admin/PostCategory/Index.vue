<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({ categories: Array });

const deleteCategory = (id) => { if (confirm('Xóa danh mục này?')) router.delete(route('admin.post-categories.destroy', id)); };
</script>

<template>
    <Head title="Danh mục Tin tức" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-2xl font-display font-bold text-white">Danh mục Tin tức</h2>
                <Link :href="route('admin.post-categories.create')" class="inline-flex items-center gap-2 rounded-xl bg-volt-500 px-5 py-2.5 text-sm font-semibold text-white hover:bg-volt-600 transition-all shadow-lg shadow-volt-500/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Thêm danh mục
                </Link>
            </div>
        </template>
        <div class="py-6">
            <div class="mx-auto max-w-7xl px-6">
                <div class="rounded-2xl border border-white/5 bg-carbon-900/50 backdrop-blur-sm p-6">
                    <template v-for="cat in categories" :key="cat.id">
                        <div class="flex justify-between items-center py-3.5 border-b border-white/5">
                            <div>
                                <span class="font-medium text-white">{{ cat.name }}</span>
                                <span class="ml-2 text-xs text-carbon-500">({{ cat.posts_count }} bài)</span>
                                <span class="ml-3 text-xs text-carbon-600 font-mono">{{ cat.slug }}</span>
                            </div>
                            <div class="flex gap-3">
                                <Link :href="route('admin.post-categories.edit', cat.id)" class="text-sm text-industrial-400 hover:text-industrial-300 transition-colors">Sửa</Link>
                                <button @click="deleteCategory(cat.id)" class="text-sm text-red-400 hover:text-red-300 transition-colors">Xóa</button>
                            </div>
                        </div>
                        <div v-if="cat.all_children?.length" class="ml-8">
                            <div v-for="child in cat.all_children" :key="child.id" class="flex justify-between items-center py-2.5 border-b border-white/[.03]">
                                <div>
                                    <span class="text-sm text-carbon-300">↳ {{ child.name }}</span>
                                    <span class="ml-3 text-xs text-carbon-600 font-mono">{{ child.slug }}</span>
                                </div>
                                <div class="flex gap-3">
                                    <Link :href="route('admin.post-categories.edit', child.id)" class="text-xs text-industrial-400 hover:text-industrial-300 transition-colors">Sửa</Link>
                                    <button @click="deleteCategory(child.id)" class="text-xs text-red-400 hover:text-red-300 transition-colors">Xóa</button>
                                </div>
                            </div>
                        </div>
                    </template>
                    <div v-if="!categories.length" class="text-center py-12 text-carbon-600">Chưa có danh mục nào.</div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
