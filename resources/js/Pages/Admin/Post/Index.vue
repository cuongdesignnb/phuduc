<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({ posts: Object, filters: Object });
const search = ref(props.filters?.search || '');
const status = ref(props.filters?.status || '');

let t;
watch([search, status], () => { clearTimeout(t); t = setTimeout(() => { router.get(route('admin.posts.index'), { search: search.value || undefined, status: status.value || undefined }, { preserveState: true, replace: true }); }, 300); });

const deletePost = (id) => { if (confirm('Xóa bài viết này?')) router.delete(route('admin.posts.destroy', id)); };
</script>

<template>
    <Head title="Quản lý Bài viết" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-2xl font-display font-bold text-white">Quản lý Bài viết</h2>
                <Link :href="route('admin.posts.create')" class="inline-flex items-center gap-2 rounded-xl bg-volt-500 px-5 py-2.5 text-sm font-semibold text-white hover:bg-volt-600 transition-all shadow-lg shadow-volt-500/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Viết bài mới
                </Link>
            </div>
        </template>
        <div class="py-6">
            <div class="mx-auto max-w-7xl px-6">
                <div class="mb-6 flex gap-4">
                    <div class="relative flex-1">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-carbon-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input v-model="search" type="text" placeholder="Tìm tiêu đề..." class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-white/10 bg-carbon-900/50 text-white placeholder-carbon-500 text-sm focus:border-volt-500/50 focus:ring-1 focus:ring-volt-500/20 transition" />
                    </div>
                    <select v-model="status" class="rounded-xl border border-white/10 bg-carbon-900/50 text-carbon-300 text-sm px-4 py-2.5 focus:border-volt-500/50 focus:ring-1 focus:ring-volt-500/20">
                        <option value="">Tất cả</option>
                        <option value="draft">Nháp</option>
                        <option value="published">Đã đăng</option>
                    </select>
                </div>
                <div class="overflow-hidden rounded-2xl border border-white/5 bg-carbon-900/50 backdrop-blur-sm">
                    <table class="min-w-full divide-y divide-white/5">
                        <thead class="bg-carbon-800/50">
                            <tr>
                                <th class="px-6 py-3.5 text-left text-xs font-medium uppercase tracking-wider text-carbon-500">Tiêu đề</th>
                                <th class="px-6 py-3.5 text-left text-xs font-medium uppercase tracking-wider text-carbon-500">Danh mục</th>
                                <th class="px-6 py-3.5 text-left text-xs font-medium uppercase tracking-wider text-carbon-500">Trạng thái</th>
                                <th class="px-6 py-3.5 text-left text-xs font-medium uppercase tracking-wider text-carbon-500">Ngày tạo</th>
                                <th class="px-6 py-3.5 text-right text-xs font-medium uppercase tracking-wider text-carbon-500">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/[.03]">
                            <tr v-for="post in posts.data" :key="post.id" class="hover:bg-white/[.02] transition-colors">
                                <td class="px-6 py-4 text-sm font-medium text-white">{{ $fixText(post.title) }}</td>
                                <td class="px-6 py-4 text-sm text-carbon-400">{{ post.category?.name ? $fixText(post.category.name) : '—' }}</td>
                                <td class="px-6 py-4"><span :class="post.status === 'published' ? 'bg-volt-500/10 text-volt-400 border-volt-500/20' : 'bg-carbon-500/10 text-carbon-400 border-carbon-500/20'" class="inline-flex rounded-lg px-2.5 py-0.5 text-xs font-medium border">{{ post.status === 'published' ? 'Đã đăng' : 'Nháp' }}</span></td>
                                <td class="px-6 py-4 text-sm text-carbon-500">{{ new Date(post.created_at).toLocaleDateString('vi-VN') }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                    <Link :href="route('admin.posts.edit', post.id)" class="text-industrial-400 hover:text-industrial-300 mr-4 transition-colors">Sửa</Link>
                                    <button @click="deletePost(post.id)" class="text-red-400 hover:text-red-300 transition-colors">Xóa</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
