<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({ warranties: Object, filters: Object });
const search = ref(props.filters?.search || '');

let t;
watch(search, () => { clearTimeout(t); t = setTimeout(() => { router.get(route('admin.warranties.index'), { search: search.value || undefined }, { preserveState: true, replace: true }); }, 300); });

const deleteWarranty = (id) => { if (confirm('Xóa bảo hành này?')) router.delete(route('admin.warranties.destroy', id)); };
const statusLabels = { active: 'Còn hạn', expired: 'Hết hạn', voided: 'Đã hủy' };
const statusColors = { active: 'bg-green-500/10 text-green-400 border border-green-500/20', expired: 'bg-carbon-500/10 text-carbon-400 border border-carbon-500/20', voided: 'bg-red-500/10 text-red-400 border border-red-500/20' };
</script>

<template>
    <Head title="Quản lý Bảo hành" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-2xl font-display font-bold text-white">Quản lý Bảo hành</h2>
                <Link :href="route('admin.warranties.create')" class="inline-flex items-center gap-1.5 rounded-xl bg-volt-500 px-5 py-2.5 text-sm font-semibold text-white hover:bg-volt-600 transition-all shadow-lg shadow-volt-500/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tạo bảo hành
                </Link>
            </div>
        </template>
        <div class="py-6">
            <div class="mx-auto max-w-7xl px-6">
                <!-- Search -->
                <div class="mb-4 relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-carbon-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input v-model="search" type="text" placeholder="Tìm serial, tên SP..." class="w-full md:w-96 rounded-xl border border-white/10 bg-carbon-800 text-white placeholder-carbon-500 py-2.5 pl-10 pr-4 text-sm focus:border-volt-500/50 focus:ring-1 focus:ring-volt-500/20 transition" />
                </div>

                <!-- Table -->
                <div class="overflow-hidden rounded-2xl border border-white/5 bg-carbon-900/50 backdrop-blur-sm">
                    <table class="min-w-full divide-y divide-white/5">
                        <thead class="bg-carbon-800/50">
                            <tr>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-carbon-400">Serial</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-carbon-400">Sản phẩm</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-carbon-400">Đơn hàng</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-carbon-400">Kích hoạt</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-carbon-400">Hết hạn</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-carbon-400">Trạng thái</th>
                                <th class="px-6 py-3.5 text-right text-xs font-semibold uppercase tracking-wider text-carbon-400">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            <tr v-for="w in warranties.data" :key="w.id" class="hover:bg-white/[.02] transition">
                                <td class="px-6 py-4 text-sm font-mono text-white">{{ w.serial_number }}</td>
                                <td class="px-6 py-4 text-sm text-white">{{ $fixText(w.product_name) }}</td>
                                <td class="px-6 py-4 text-sm text-carbon-400">{{ w.order?.order_number || '—' }}</td>
                                <td class="px-6 py-4 text-sm text-carbon-400">{{ w.activation_date || '—' }}</td>
                                <td class="px-6 py-4 text-sm text-carbon-400">{{ w.expiration_date || '—' }}</td>
                                <td class="px-6 py-4"><span :class="statusColors[w.status]" class="inline-flex rounded-lg px-2.5 py-0.5 text-xs font-semibold">{{ statusLabels[w.status] }}</span></td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                    <Link :href="route('admin.warranties.edit', w.id)" class="text-industrial-400 hover:text-industrial-300 mr-3 transition">Sửa</Link>
                                    <button @click="deleteWarranty(w.id)" class="text-red-400 hover:text-red-300 transition">Xóa</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
