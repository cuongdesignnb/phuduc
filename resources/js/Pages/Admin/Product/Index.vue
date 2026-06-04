<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    products: Object,
    filters: Object,
});

const search = ref(props.filters?.search || '');
const status = ref(props.filters?.status || '');

let searchTimeout;
watch([search, status], () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('admin.products.index'), {
            search: search.value || undefined,
            status: status.value || undefined,
        }, { preserveState: true, replace: true });
    }, 300);
});

const deleteProduct = (id) => {
    if (confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')) {
        router.delete(route('admin.products.destroy', id));
    }
};

const formatPrice = (price) => {
    if (!price || price == 0) return 'Liên hệ';
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price);
};
</script>

<template>
    <Head title="Quản lý Sản phẩm" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-2xl font-display font-bold text-white">Quản lý Sản phẩm</h2>
                <Link :href="route('admin.products.create')" class="inline-flex items-center gap-2 rounded-xl bg-volt-500 px-5 py-2.5 text-sm font-semibold text-white hover:bg-volt-600 transition-all shadow-lg shadow-volt-500/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Thêm sản phẩm
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-6">
                <!-- Filters -->
                <div class="mb-6 flex gap-4">
                    <div class="relative flex-1">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-carbon-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input v-model="search" type="text" placeholder="Tìm theo tên, SKU..."
                            class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-white/10 bg-carbon-900/50 text-white placeholder-carbon-500 text-sm focus:border-volt-500/50 focus:ring-1 focus:ring-volt-500/20 transition" />
                    </div>
                    <select v-model="status" class="rounded-xl border border-white/10 bg-carbon-900/50 text-carbon-300 text-sm px-4 py-2.5 focus:border-volt-500/50 focus:ring-1 focus:ring-volt-500/20">
                        <option value="">Tất cả trạng thái</option>
                        <option value="active">Đang bán</option>
                        <option value="inactive">Ngừng bán</option>
                    </select>
                </div>

                <div class="overflow-hidden rounded-2xl border border-white/5 bg-carbon-900/50 backdrop-blur-sm">
                    <table class="min-w-full divide-y divide-white/5">
                        <thead class="bg-carbon-800/50">
                            <tr>
                                <th class="px-6 py-3.5 text-left text-xs font-medium uppercase tracking-wider text-carbon-500">Ảnh</th>
                                <th class="px-6 py-3.5 text-left text-xs font-medium uppercase tracking-wider text-carbon-500">Tên SP</th>
                                <th class="px-6 py-3.5 text-left text-xs font-medium uppercase tracking-wider text-carbon-500">SKU</th>
                                <th class="px-6 py-3.5 text-left text-xs font-medium uppercase tracking-wider text-carbon-500">Giá</th>
                                <th class="px-6 py-3.5 text-left text-xs font-medium uppercase tracking-wider text-carbon-500">Kho</th>
                                <th class="px-6 py-3.5 text-left text-xs font-medium uppercase tracking-wider text-carbon-500">Trạng thái</th>
                                <th class="px-6 py-3.5 text-right text-xs font-medium uppercase tracking-wider text-carbon-500">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/[.03]">
                            <tr v-for="product in products.data" :key="product.id" class="hover:bg-white/[.02] transition-colors">
                                <td class="whitespace-nowrap px-6 py-4">
                                    <img v-if="product.images?.length" :src="'/storage/' + product.images[0].image_path" class="w-12 h-12 object-cover rounded-lg border border-white/10" />
                                    <div v-else class="w-12 h-12 bg-carbon-800 rounded-lg flex items-center justify-center text-xs text-carbon-600 border border-white/5">N/A</div>
                                </td>
                                <td class="px-6 py-4 text-sm font-medium text-white">{{ product.name }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-carbon-400 font-mono">{{ product.sku || '—' }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-volt-400 font-semibold">{{ formatPrice(product.price) }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-carbon-400">{{ product.stock }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm">
                                    <span :class="product.status === 'active' ? 'bg-volt-500/10 text-volt-400 border-volt-500/20' : 'bg-red-500/10 text-red-400 border-red-500/20'" class="inline-flex rounded-lg px-2.5 py-0.5 text-xs font-medium border">
                                        {{ product.status === 'active' ? 'Đang bán' : 'Ngừng bán' }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                    <Link :href="route('admin.products.edit', product.id)" class="text-industrial-400 hover:text-industrial-300 mr-4 transition-colors">Sửa</Link>
                                    <button @click="deleteProduct(product.id)" class="text-red-400 hover:text-red-300 transition-colors">Xóa</button>
                                </td>
                            </tr>
                            <tr v-if="!products.data.length">
                                <td colspan="7" class="px-6 py-12 text-center text-sm text-carbon-600">Không tìm thấy sản phẩm nào.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="products.links?.length > 3" class="mt-6 flex justify-center gap-1">
                    <Link v-for="link in products.links" :key="link.label"
                        :href="link.url || '#'"
                        :class="[link.active ? 'bg-volt-500/20 text-volt-400 border-volt-500/30' : 'text-carbon-400 border-white/5 hover:bg-white/5 hover:text-white', !link.url ? 'opacity-30 cursor-not-allowed' : '']"
                        class="px-3.5 py-1.5 rounded-lg text-sm border transition-colors"
                        v-html="link.label"
                        preserve-state
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
