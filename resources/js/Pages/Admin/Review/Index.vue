<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({ reviews: Object, filters: Object });
const status = ref(props.filters?.status || '');

watch(status, () => {
    router.get(route('admin.reviews.index'), { status: status.value || undefined }, { preserveState: true, replace: true });
});

const updateStatus = (id, newStatus) => {
    router.patch(route('admin.reviews.updateStatus', id), { status: newStatus }, { preserveScroll: true });
};

const deleteReview = (id) => {
    if (confirm('Xóa đánh giá này?')) router.delete(route('admin.reviews.destroy', id), { preserveScroll: true });
};

const statusLabels = { pending: 'Chờ duyệt', approved: 'Đã duyệt', rejected: 'Từ chối' };
const statusColors = { pending: 'bg-amber-500/10 text-amber-400 border-amber-500/20', approved: 'bg-volt-500/10 text-volt-400 border-volt-500/20', rejected: 'bg-red-500/10 text-red-400 border-red-500/20' };
</script>

<template>
    <Head title="Quản lý Đánh giá" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-display font-bold text-white">Quản lý Đánh giá</h2>
        </template>
        <div class="py-6">
            <div class="mx-auto max-w-7xl px-6">
                <div class="mb-6">
                    <select v-model="status" class="rounded-xl border border-white/10 bg-carbon-900/50 text-carbon-300 text-sm px-4 py-2.5 focus:border-volt-500/50 focus:ring-1 focus:ring-volt-500/20">
                        <option value="">Tất cả</option>
                        <option v-for="(label, key) in statusLabels" :key="key" :value="key">{{ label }}</option>
                    </select>
                </div>
                <div class="space-y-4">
                    <div v-for="review in reviews.data" :key="review.id" class="rounded-2xl border border-white/5 bg-carbon-900/50 backdrop-blur-sm p-6 hover:border-white/10 transition-all">
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="font-medium text-white">{{ review.customer_name }}</span>
                                    <span class="text-amber-400 text-sm">{{ '★'.repeat(review.rating) }}{{ '☆'.repeat(5 - review.rating) }}</span>
                                    <span :class="statusColors[review.status]" class="inline-flex rounded-lg px-2.5 py-0.5 text-xs font-medium border">{{ statusLabels[review.status] }}</span>
                                </div>
                                <p class="text-sm text-carbon-400 mb-1">Sản phẩm: <span class="text-carbon-300">{{ review.product?.name || 'N/A' }}</span></p>
                                <p class="text-sm text-carbon-300">{{ review.content }}</p>
                                <p class="text-xs text-carbon-600 mt-2">{{ new Date(review.created_at).toLocaleString('vi-VN') }}</p>
                            </div>
                            <div class="flex gap-2 shrink-0">
                                <button v-if="review.status !== 'approved'" @click="updateStatus(review.id, 'approved')" class="text-xs bg-volt-500/10 text-volt-400 border border-volt-500/20 px-3 py-1.5 rounded-lg hover:bg-volt-500/20 transition">Duyệt</button>
                                <button v-if="review.status !== 'rejected'" @click="updateStatus(review.id, 'rejected')" class="text-xs bg-red-500/10 text-red-400 border border-red-500/20 px-3 py-1.5 rounded-lg hover:bg-red-500/20 transition">Từ chối</button>
                                <button @click="deleteReview(review.id)" class="text-xs text-red-400 hover:text-red-300 px-2 transition-colors">Xóa</button>
                            </div>
                        </div>
                    </div>
                    <div v-if="!reviews.data.length" class="text-center py-12 text-carbon-600">Không có đánh giá nào.</div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
