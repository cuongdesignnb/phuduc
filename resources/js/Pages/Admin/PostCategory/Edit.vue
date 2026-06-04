<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AdvancedTextEditor from '@/Components/AdvancedTextEditor.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({ category: Object, parents: Array });

const form = useForm({
    name: props.category?.name || '',
    slug: props.category?.slug || '',
    parent_id: props.category?.parent_id || '',
    description: props.category?.description || '',
});

const save = () => {
    if (props.category) {
        form.put(route('admin.post-categories.update', props.category.id));
    } else {
        form.post(route('admin.post-categories.store'));
    }
};
</script>

<template>
    <Head :title="category ? 'Sửa danh mục' : 'Tạo danh mục'" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-display font-bold text-white">{{ category ? 'Sửa danh mục' : 'Tạo danh mục mới' }}</h2>
        </template>
        <div class="py-6">
            <div class="mx-auto max-w-3xl px-6">
                <form @submit.prevent="save" class="rounded-2xl border border-white/5 bg-carbon-900/50 backdrop-blur-sm p-6 space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-carbon-300 mb-1.5">Tên danh mục *</label>
                            <input v-model="form.name" type="text" required class="w-full rounded-xl border border-white/10 bg-carbon-800 text-white placeholder-carbon-500 py-2.5 px-4 text-sm focus:border-volt-500/50 focus:ring-1 focus:ring-volt-500/20 transition" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-carbon-300 mb-1.5">Slug</label>
                            <input v-model="form.slug" type="text" placeholder="Tự sinh nếu bỏ trống" class="w-full rounded-xl border border-white/10 bg-carbon-800 text-white placeholder-carbon-500 py-2.5 px-4 text-sm focus:border-volt-500/50 focus:ring-1 focus:ring-volt-500/20 transition" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-carbon-300 mb-1.5">Danh mục cha</label>
                            <select v-model="form.parent_id" class="w-full rounded-xl border border-white/10 bg-carbon-800 text-white py-2.5 px-4 text-sm focus:border-volt-500/50 focus:ring-1 focus:ring-volt-500/20 transition">
                                <option value="">— Không (Root) —</option>
                                <option v-for="p in parents" :key="p.id" :value="p.id">{{ p.name }}</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-carbon-300 mb-1.5">Mô tả</label>
                        <AdvancedTextEditor v-model="form.description" />
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" :disabled="form.processing" class="inline-flex items-center gap-2 rounded-xl bg-volt-500 px-6 py-2.5 text-sm font-semibold text-white hover:bg-volt-600 disabled:opacity-50 transition-all shadow-lg shadow-volt-500/20">
                            {{ category ? 'Cập nhật' : 'Tạo danh mục' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
