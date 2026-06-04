<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import MenuItemTree from '@/Components/MenuItemTree.vue';

const props = defineProps({
    menu: Object,
});

const fixText = (value) => {
    if (typeof value !== 'string') return value;
    const codes = Array.from(value).map((char) => char.charCodeAt(0));
    const isBroken = codes.some((code) => [0xc2, 0xc3, 0xc4, 0xc6, 0xca, 0xfffd].includes(code))
        || codes.some((code) => code >= 0x80 && code <= 0x9f)
        || codes.some((code, index) => code === 0xe1 && [0xba, 0xbb].includes(codes[index + 1]));
    if (!isBroken) return value;
    try {
        const bytes = Uint8Array.from(Array.from(value), (char) => char.charCodeAt(0) & 255);
        return new TextDecoder('utf-8', { fatal: false }).decode(bytes);
    } catch {
        return value;
    }
};

const form = useForm({
    name: props.menu?.name ? fixText(props.menu.name) : '',
    location: props.menu?.location || '',
});

const menuItems = ref(buildTree(props.menu?.items || []));

function buildTree(items) {
    return items.map(item => ({
        id: item.id,
        title: item.title ? fixText(item.title) : '',
        url: item.url || '',
        model_type: item.model_type || '',
        model_id: item.model_id || null,
        children: item.all_children ? buildTree(item.all_children) : (item.children ? buildTree(item.children) : []),
    }));
}

const saveMenu = () => {
    if (props.menu) {
        form.put(route('admin.menus.update', props.menu.id));
    } else {
        form.post(route('admin.menus.store'));
    }
};

const saveItems = () => {
    if (!props.menu) return;
    router.post(route('admin.menus.items', props.menu.id), {
        items: menuItems.value,
    }, {
        preserveScroll: true,
    });
};

const addItem = () => {
    menuItems.value.push({
        id: Date.now(),
        title: 'Mục mới',
        url: '',
        model_type: '',
        model_id: null,
        children: [],
    });
};
</script>

<template>
    <Head :title="menu ? 'Sửa Menu: ' + $fixText(menu.name) : 'Tạo Menu'" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-display font-bold text-white">
                {{ menu ? 'Sửa Menu: ' + $fixText(menu.name) : 'Tạo Menu mới' }}
            </h2>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-6 space-y-6">
                <!-- Menu Info -->
                <div class="rounded-2xl border border-white/5 bg-carbon-900/50 backdrop-blur-sm p-6">
                    <h3 class="text-lg font-display font-semibold text-white mb-4">Thông tin Menu</h3>
                    <form @submit.prevent="saveMenu" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-carbon-300 mb-1.5">Tên Menu</label>
                                <input v-model="form.name" type="text" required class="w-full rounded-xl border border-white/10 bg-carbon-800 text-white py-2.5 px-4 text-sm focus:border-volt-500/50 focus:ring-1 focus:ring-volt-500/20 transition" />
                                <p v-if="form.errors.name" class="mt-1 text-sm text-red-400">{{ form.errors.name }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-carbon-300 mb-1.5">Vị trí (location)</label>
                                <input v-model="form.location" type="text" placeholder="header, footer, sidebar..." class="w-full rounded-xl border border-white/10 bg-carbon-800 text-white placeholder-carbon-500 py-2.5 px-4 text-sm focus:border-volt-500/50 focus:ring-1 focus:ring-volt-500/20 transition" />
                                <p v-if="form.errors.location" class="mt-1 text-sm text-red-400">{{ form.errors.location }}</p>
                            </div>
                        </div>
                        <button type="submit" :disabled="form.processing" class="inline-flex items-center gap-2 rounded-xl bg-volt-500 px-5 py-2.5 text-sm font-semibold text-white hover:bg-volt-600 disabled:opacity-50 transition-all shadow-lg shadow-volt-500/20">
                            {{ menu ? 'Cập nhật Menu' : 'Tạo Menu' }}
                        </button>
                    </form>
                </div>

                <!-- Menu Items Builder -->
                <div v-if="menu" class="rounded-2xl border border-white/5 bg-carbon-900/50 backdrop-blur-sm p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-display font-semibold text-white">Cấu trúc Menu (Kéo thả)</h3>
                        <div class="flex gap-2">
                            <button @click="addItem" class="inline-flex items-center gap-1.5 rounded-xl border border-white/10 bg-carbon-800 px-4 py-2 text-sm font-medium text-carbon-300 hover:bg-carbon-700 hover:text-white transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Thêm mục
                            </button>
                            <button @click="saveItems" class="inline-flex items-center gap-1.5 rounded-xl bg-volt-500 px-4 py-2 text-sm font-semibold text-white hover:bg-volt-600 transition-all shadow-lg shadow-volt-500/20">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Lưu cấu trúc
                            </button>
                        </div>
                    </div>

                    <div v-if="menuItems.length === 0" class="text-center py-12 text-carbon-600 border-2 border-dashed border-white/10 rounded-xl">
                        Chưa có mục nào. Nhấn "Thêm mục" để bắt đầu.
                    </div>

                    <MenuItemTree v-model="menuItems" :depth="0" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
