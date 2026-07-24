<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AdminConfirmDialog from '@/Components/Admin/AdminConfirmDialog.vue';
import AdminDataCard from '@/Components/Admin/AdminDataCard.vue';
import AdminPageHeader from '@/Components/Admin/AdminPageHeader.vue';
import AdminPagination from '@/Components/Admin/AdminPagination.vue';
import AdminStatusBadge from '@/Components/Admin/AdminStatusBadge.vue';
import AdminTable from '@/Components/Admin/AdminTable.vue';
import AdminTextInput from '@/Components/Admin/AdminTextInput.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
const props = defineProps({ page: { type: Object, required: true } });
const module = props.page.module; const search = ref(module.filters.search || ''); const status = ref(module.filters.status || ''); const deleting = ref(null); let timer;
watch([search, status], () => { clearTimeout(timer); timer = setTimeout(() => router.get(route('admin.posts.index'), { search: search.value || undefined, status: status.value || undefined }, { preserveState: true, replace: true }), 300); });
const destroy = () => router.delete(deleting.value.delete_url, { preserveScroll: true, onFinish: () => { deleting.value = null; } });
</script>

<template><Head :title="page.meta.title" /><AuthenticatedLayout><AdminPageHeader :title="page.meta.title" description="Quản lý bài viết và nội dung biên tập"><Link :href="route('admin.posts.create')" class="rounded-lg bg-admin-accent px-4 py-2 text-sm font-semibold text-admin-page">Thêm bài viết</Link></AdminPageHeader><AdminDataCard title="Bộ lọc" class="mt-6"><div class="grid gap-3 sm:grid-cols-2"><AdminTextInput v-model="search" placeholder="Tìm tiêu đề hoặc tóm tắt" aria-label="Tìm bài viết" /><select v-model="status" class="border border-admin-border bg-admin-page px-3 py-2 text-sm text-admin-content" aria-label="Lọc trạng thái"><option value="">Tất cả trạng thái</option><option value="draft">Bản nháp</option><option value="published">Đã đăng</option></select></div></AdminDataCard><AdminDataCard title="Danh sách bài viết" class="mt-6"><AdminTable :columns="[{ key: 'title', label: 'Tiêu đề' }, { key: 'category', label: 'Danh mục' }, { key: 'status', label: 'Trạng thái' }, { key: 'date', label: 'Cập nhật' }, { key: 'actions', label: 'Thao tác' }]"><tr v-for="post in module.items" :key="post.id" class="border-b border-admin-border"><td class="px-3 py-3"><Link :href="post.edit_url" class="font-medium text-admin-content hover:text-admin-accent">{{ post.title }}</Link><p class="text-xs text-admin-content-muted">{{ post.slug }}</p></td><td class="px-3 py-3 text-sm text-admin-content">{{ post.category?.name || 'Chưa phân loại' }}</td><td class="px-3 py-3"><AdminStatusBadge :status="post.status" /></td><td class="px-3 py-3 text-sm text-admin-content-muted">{{ post.updated_at_display }}</td><td class="px-3 py-3 text-right"><Link :href="post.edit_url" class="mr-3 text-sm text-admin-accent">Sửa</Link><button type="button" class="text-sm text-admin-danger" @click="deleting = post">Xóa</button></td></tr><tr v-if="!module.items.length"><td colspan="5" class="px-3 py-10 text-center text-sm text-admin-content-muted">Chưa có bài viết.</td></tr></AdminTable><AdminPagination class="mt-4" :pagination="module.pagination" /></AdminDataCard><AdminConfirmDialog :open="!!deleting" title="Xóa bài viết" message="Bài viết đang được dùng ở trang chủ hoặc menu sẽ không thể xóa." confirm-label="Xóa bài viết" danger @cancel="deleting = null" @confirm="destroy" /></AuthenticatedLayout></template>
