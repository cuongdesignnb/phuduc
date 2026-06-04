<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({ warranty: Object, orders: Array });

const form = useForm({
    serial_number: props.warranty?.serial_number || '',
    product_name: props.warranty?.product_name || '',
    order_id: props.warranty?.order_id || '',
    activation_date: props.warranty?.activation_date || '',
    expiration_date: props.warranty?.expiration_date || '',
    status: props.warranty?.status || 'active',
});

const save = () => {
    if (props.warranty) {
        form.put(route('admin.warranties.update', props.warranty.id));
    } else {
        form.post(route('admin.warranties.store'));
    }
};
</script>

<template>
    <Head :title="warranty ? 'Sửa Bảo hành' : 'Tạo Bảo hành'" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-display font-bold text-white">{{ warranty ? 'Sửa Bảo hành' : 'Tạo Bảo hành mới' }}</h2>
        </template>
        <div class="py-6">
            <div class="mx-auto max-w-3xl px-6">
                <form @submit.prevent="save" class="rounded-2xl border border-white/5 bg-carbon-900/50 backdrop-blur-sm p-6 space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-carbon-300 mb-1.5">Serial Number *</label>
                            <input v-model="form.serial_number" type="text" required class="w-full rounded-xl border border-white/10 bg-carbon-800 text-white py-2.5 px-4 text-sm focus:border-volt-500/50 focus:ring-1 focus:ring-volt-500/20 transition" />
                            <p v-if="form.errors.serial_number" class="mt-1 text-sm text-red-400">{{ form.errors.serial_number }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-carbon-300 mb-1.5">Tên sản phẩm *</label>
                            <input v-model="form.product_name" type="text" required class="w-full rounded-xl border border-white/10 bg-carbon-800 text-white py-2.5 px-4 text-sm focus:border-volt-500/50 focus:ring-1 focus:ring-volt-500/20 transition" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-carbon-300 mb-1.5">Đơn hàng</label>
                            <select v-model="form.order_id" class="w-full rounded-xl border border-white/10 bg-carbon-800 text-white py-2.5 px-4 text-sm focus:border-volt-500/50 focus:ring-1 focus:ring-volt-500/20 transition">
                                <option value="">— Không liên kết —</option>
                                <option v-for="o in orders" :key="o.id" :value="o.id">{{ o.order_number }} — {{ o.customer_name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-carbon-300 mb-1.5">Trạng thái</label>
                            <select v-model="form.status" class="w-full rounded-xl border border-white/10 bg-carbon-800 text-white py-2.5 px-4 text-sm focus:border-volt-500/50 focus:ring-1 focus:ring-volt-500/20 transition">
                                <option value="active">Còn hạn</option>
                                <option value="expired">Hết hạn</option>
                                <option value="voided">Đã hủy</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-carbon-300 mb-1.5">Ngày kích hoạt</label>
                            <input v-model="form.activation_date" type="date" class="w-full rounded-xl border border-white/10 bg-carbon-800 text-white py-2.5 px-4 text-sm focus:border-volt-500/50 focus:ring-1 focus:ring-volt-500/20 transition" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-carbon-300 mb-1.5">Ngày hết hạn</label>
                            <input v-model="form.expiration_date" type="date" class="w-full rounded-xl border border-white/10 bg-carbon-800 text-white py-2.5 px-4 text-sm focus:border-volt-500/50 focus:ring-1 focus:ring-volt-500/20 transition" />
                        </div>
                    </div>
                    <div class="flex justify-end pt-2">
                        <button type="submit" :disabled="form.processing" class="inline-flex items-center gap-2 rounded-xl bg-volt-500 px-6 py-2.5 text-sm font-semibold text-white hover:bg-volt-600 disabled:opacity-50 transition-all shadow-lg shadow-volt-500/20">
                            {{ warranty ? 'Cập nhật' : 'Tạo bảo hành' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
