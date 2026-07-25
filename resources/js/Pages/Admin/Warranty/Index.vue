<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({ warranties: Object, filters: Object });
const search = ref(props.filters?.search || '');
const deleting = ref(null);

let t;
watch(search, () => { clearTimeout(t); t = setTimeout(() => { router.get(route('admin.warranties.index'), { search: search.value || undefined }, { preserveState: true, replace: true }); }, 300); });

const deleteWarranty = (id) => { deleting.value = id; };
const confirmDelete = () => router.delete(route('admin.warranties.destroy', deleting.value), { onFinish: () => { deleting.value = null; } });
const statusLabels = { active: 'Còn hạn', expired: 'Hết hạn', voided: 'Đã hủy' };
const statusColors = { active: 'bg-green-500/10 text-green-400 border border-green-500/20', expired: 'bg-admin-content-muted/10 text-admin-content-muted border border-admin-content-muted/20', voided: 'bg-red-500/10 text-red-400 border border-red-500/20' };
</script>

<template>
    <Head title="Quản lý Bảo hành" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-2xl font-display font-bold text-white">Quản lý Bảo hành</h2>
                <Link :href="route('admin.warranties.create')" class="inline-flex items-center gap-1.5 rounded-xl bg-admin-accent px-5 py-2.5 text-sm font-semibold text-white hover:bg-admin-accent-hover transition-all shadow-lg shadow-admin-accent/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tạo bảo hành
                </Link>
            </div>
        </template>
        <div class="py-6">
            <div class="mx-auto max-w-7xl px-6">
                <!-- Search -->
                <div class="mb-4 relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-admin-content-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input v-model="search" type="text" placeholder="Tìm serial, tên SP..." class="w-full md:w-96 rounded-xl border border-white/10 bg-admin-surface-muted text-white placeholder-admin-content-muted py-2.5 pl-10 pr-4 text-sm focus:border-admin-accent/50 focus:ring-1 focus:ring-admin-accent/20 transition" />
                </div>

                <!-- Table -->
                <div class="overflow-hidden rounded-2xl border border-white/5 bg-admin-surface/50 backdrop-blur-sm">
                    <table class="min-w-full divide-y divide-white/5">
                        <thead class="bg-admin-surface-muted/50">
                            <tr>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-admin-content-muted">Serial</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-admin-content-muted">Sản phẩm</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-admin-content-muted">Đơn hàng</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-admin-content-muted">Kích hoạt</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-admin-content-muted">Hết hạn</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-admin-content-muted">Trạng thái</th>
                                <th class="px-6 py-3.5 text-right text-xs font-semibold uppercase tracking-wider text-admin-content-muted">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            <tr v-for="w in warranties.data" :key="w.id" class="hover:bg-white/[.02] transition">
                                <td class="px-6 py-4 text-sm font-mono text-white">{{ w.serial_number }}</td>
                                <td class="px-6 py-4 text-sm text-white">{{ $fixText(w.product_name) }}</td>
                                <td class="px-6 py-4 text-sm text-admin-content-muted">{{ w.order?.order_number || '—' }}</td>
                                <td class="px-6 py-4 text-sm text-admin-content-muted">{{ w.activation_date || '—' }}</td>
                                <td class="px-6 py-4 text-sm text-admin-content-muted">{{ w.expiration_date || '—' }}</td>
                                <td class="px-6 py-4"><span :class="statusColors[w.status]" class="inline-flex rounded-lg px-2.5 py-0.5 text-xs font-semibold">{{ statusLabels[w.status] }}</span></td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                    <Link :href="route('admin.warranties.edit', w.id)" class="text-admin-accent hover:text-admin-accent-hover mr-3 transition">Sửa</Link>
                                    <button @click="deleteWarranty(w.id)" class="text-red-400 hover:text-red-300 transition">Xóa</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <AdminConfirmDialog :open="!!deleting" title="Xóa bảo hành" message="Bạn có chắc muốn xóa bảo hành này không?" confirm-label="Xóa bảo hành" danger @cancel="deleting = null" @confirm="confirmDelete" />
    </AuthenticatedLayout>
</template>
