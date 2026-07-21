<script setup>
import MediaBox from '@/Components/MediaBox.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import draggable from 'vuedraggable';
import { computed, ref } from 'vue';

const props = defineProps({
    sections: { type: Array, required: true },
    registry: { type: Object, required: true },
    products: { type: Array, default: () => [] },
    posts: { type: Array, default: () => [] },
});

const clone = (value) => JSON.parse(JSON.stringify(value));
let nextItemKey = 0;
const sections = ref(clone(props.sections).map((section) => ({
    ...section,
    items: section.items.map((item) => ({ ...item, _key: `item-${item.id || `new-${nextItemKey++}`}` })),
})));
const activeKey = ref(sections.value[0]?.key);
const saving = ref(false);
const productSearch = ref('');
const showMediaBox = ref(false);
const mediaTarget = ref(null);
const page = usePage();

const activeSection = computed(() => sections.value.find((section) => section.key === activeKey.value));
const activeDefinition = computed(() => props.registry[activeKey.value] || {});
const errors = computed(() => page.props.errors || {});
const sourceOptionLabel = (option) => {
    if (option === 'manual') return 'Chọn thủ công';
    if (activeKey.value === 'featured_products') return 'Sản phẩm mới nhất';
    if (activeKey.value === 'latest_posts') return 'Bài viết mới nhất';
    return option;
};

const fieldLabels = {
    title: 'Tiêu đề / Tên',
    subtitle: 'Phụ đề / Vai trò',
    description: 'Mô tả / Nội dung',
    image: 'Ảnh / Logo',
    icon: 'Icon',
    url: 'URL',
    tone: 'Tone',
    avatar_text: 'Avatar fallback',
};

const getPath = (object, path) => path.split('.').reduce((value, key) => value?.[key], object);
const setPath = (object, path, value) => {
    const parts = path.split('.');
    let target = object;
    parts.forEach((part, index) => {
        if (index === parts.length - 1) {
            target[part] = value;
            return;
        }
        const nextPart = parts[index + 1];
        if (target[part] === undefined || target[part] === null) {
            target[part] = /^\d+$/.test(nextPart) ? [] : {};
        }
        target = target[part];
    });
};

const addItem = () => {
    activeSection.value.items.push({
        id: null,
        _key: `item-new-${nextItemKey++}`,
        title: '',
        subtitle: '',
        description: '',
        image: '',
        icon: '',
        url: '',
        metadata: {},
        enabled: true,
        sort_order: activeSection.value.items.length,
    });
};

const removeItem = (index) => activeSection.value.items.splice(index, 1);
const itemValue = (item, field) => ['tone', 'avatar_text'].includes(field) ? item.metadata?.[field] : item[field];
const setItemValue = (item, field, value) => {
    if (['tone', 'avatar_text'].includes(field)) {
        item.metadata ||= {};
        item.metadata[field] = value;
    } else {
        item[field] = value;
    }
};

const metadataItemFields = new Set(['tone', 'avatar_text']);
const businessItemFields = new Set(['title', 'subtitle', 'description', 'image', 'icon', 'url']);
const itemPayload = (item, definition) => {
    const payload = {
        id: item.id ?? null,
        enabled: Boolean(item.enabled),
        sort_order: item.sort_order,
        metadata: {},
    };

    (definition.item_fields || []).forEach((field) => {
        if (metadataItemFields.has(field)) {
            if (Object.prototype.hasOwnProperty.call(item.metadata || {}, field)) {
                payload.metadata[field] = item.metadata[field];
            }
            return;
        }

        if (businessItemFields.has(field)) {
            payload[field] = item[field] ?? null;
        }
    });

    return payload;
};

const filteredProducts = computed(() => {
    const search = productSearch.value.trim().toLocaleLowerCase('vi');
    const selected = new Set(activeSection.value?.config?.product_ids || []);
    return props.products.filter((product) => !selected.has(product.id)
        && (!search || `${product.name} ${product.sku || ''}`.toLocaleLowerCase('vi').includes(search)));
});

const selectedProductModels = computed({
    get: () => (activeSection.value?.config?.product_ids || [])
        .map((id) => props.products.find((product) => product.id === id))
        .filter(Boolean),
    set: (products) => { activeSection.value.config.product_ids = products.map((product) => product.id); },
});

const addProduct = (product) => activeSection.value.config.product_ids.push(product.id);
const removeProduct = (productId) => {
    activeSection.value.config.product_ids = activeSection.value.config.product_ids.filter((id) => id !== productId);
};

const togglePost = (postId) => {
    const ids = activeSection.value.config.post_ids || [];
    activeSection.value.config.post_ids = ids.includes(postId) ? ids.filter((id) => id !== postId) : [...ids, postId];
};

const openConfigMedia = (path) => {
    mediaTarget.value = { kind: 'config', path };
    showMediaBox.value = true;
};

const openItemMedia = (item) => {
    mediaTarget.value = { kind: 'item', item };
    showMediaBox.value = true;
};

const onMediaSelected = (media) => {
    if (mediaTarget.value?.kind === 'config') setPath(activeSection.value.config, mediaTarget.value.path, media.file_path || '');
    if (mediaTarget.value?.kind === 'item') mediaTarget.value.item.image = media.file_path || '';
    showMediaBox.value = false;
};

const save = () => {
    sections.value.forEach((section, sectionIndex) => {
        section.sort_order = sectionIndex * 10;
        section.items.forEach((item, itemIndex) => { item.sort_order = itemIndex; });
    });
    const payload = sections.value.map((section) => ({
        ...section,
        items: section.items.map((item) => itemPayload(item, props.registry[section.key] || {})),
    }));
    saving.value = true;
    router.post(route('admin.home-content.save'), { sections: payload }, {
        preserveScroll: true,
        onFinish: () => { saving.value = false; },
    });
};
</script>

<template>
    <Head title="Nội dung trang chủ" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-4">
                <div><h2 class="text-2xl font-bold text-white">Nội dung trang chủ</h2><p class="mt-1 text-sm text-carbon-400">Một nơi duy nhất để quản lý nội dung và thứ tự homepage.</p></div>
                <button @click="save" :disabled="saving" class="rounded-xl bg-volt-500 px-5 py-2.5 text-sm font-semibold text-white disabled:opacity-50">{{ saving ? 'Đang lưu...' : 'Lưu nội dung' }}</button>
            </div>
        </template>

        <div class="mx-auto grid max-w-7xl gap-6 px-6 py-6 lg:grid-cols-[300px_1fr]">
            <aside class="self-start rounded-2xl border border-white/5 bg-carbon-900/60 p-3 lg:sticky lg:top-6">
                <p class="mb-3 px-2 text-xs font-bold uppercase tracking-wider text-carbon-500">Kéo để đổi thứ tự section</p>
                <draggable v-model="sections" item-key="key" handle=".section-handle" class="space-y-2">
                    <template #item="{ element }">
                        <button @click="activeKey = element.key" class="section-handle flex w-full cursor-move items-center justify-between rounded-xl px-3 py-3 text-left text-sm font-semibold" :class="activeKey === element.key ? 'bg-volt-500/15 text-volt-400' : 'text-carbon-300 hover:bg-white/5'">
                            <span>{{ registry[element.key].label }}</span><span :class="element.enabled ? 'text-emerald-400' : 'text-carbon-600'">●</span>
                        </button>
                    </template>
                </draggable>
            </aside>

            <main v-if="activeSection" class="space-y-5">
                <div v-if="Object.keys(errors).length" class="rounded-xl border border-red-500/30 bg-red-500/10 p-4 text-sm text-red-200">
                    <p class="font-bold">Không thể lưu nội dung:</p><p v-for="(message, key) in errors" :key="key">{{ message }}</p>
                </div>

                <section class="rounded-2xl border border-white/5 bg-carbon-900/60 p-5">
                    <div class="mb-5 flex items-start justify-between gap-4">
                        <div><h3 class="text-lg font-bold text-white">{{ activeDefinition.label }}</h3><p class="text-sm text-carbon-500">{{ activeSection.key }} · {{ activeSection.type }}</p></div>
                        <label class="flex items-center gap-2 text-sm text-carbon-300"><input v-model="activeSection.enabled" type="checkbox" class="rounded bg-carbon-800 text-volt-500" /> Bật section</label>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <label v-if="activeDefinition.heading_fields.includes('title')"><span class="mb-1 block text-sm text-carbon-300">Tiêu đề</span><input v-model="activeSection.heading.title" class="admin-input" /></label>
                        <label v-if="activeDefinition.heading_fields.includes('subtitle')"><span class="mb-1 block text-sm text-carbon-300">Phụ đề</span><input v-model="activeSection.heading.subtitle" class="admin-input" /></label>
                        <label v-if="activeDefinition.heading_fields.includes('description')" class="md:col-span-2"><span class="mb-1 block text-sm text-carbon-300">Mô tả</span><textarea v-model="activeSection.heading.description" rows="3" class="admin-input"></textarea></label>
                        <label><span class="mb-1 block text-sm text-carbon-300">Variant</span><select v-model="activeSection.variant" class="admin-input"><option v-for="variant in activeDefinition.allowed_variants" :key="variant" :value="variant">{{ variant }}</option></select></label>
                    </div>
                </section>

                <section v-if="activeDefinition.config_fields?.length" class="rounded-2xl border border-white/5 bg-carbon-900/60 p-5">
                    <h3 class="mb-4 text-lg font-bold text-white">Cấu hình section</h3>
                    <div class="grid gap-4 md:grid-cols-2">
                        <template v-for="field in activeDefinition.config_fields" :key="field.key">
                            <label v-if="field.control === 'text' || field.control === 'number'"><span class="mb-1 block text-sm text-carbon-300">{{ field.label }}</span><input :type="field.control" :value="getPath(activeSection.config, field.key)" @input="setPath(activeSection.config, field.key, field.control === 'number' ? Number($event.target.value) : $event.target.value)" class="admin-input" /></label>
                            <label v-else-if="field.control === 'select'"><span class="mb-1 block text-sm text-carbon-300">{{ field.label }}</span><select :value="getPath(activeSection.config, field.key)" @change="setPath(activeSection.config, field.key, $event.target.value)" class="admin-input"><option v-for="option in field.options" :key="option" :value="option">{{ sourceOptionLabel(option) }}</option></select></label>
                            <div v-else-if="field.control === 'media'" class="md:col-span-2"><span class="mb-1 block text-sm text-carbon-300">{{ field.label }}</span><div class="flex gap-2"><input :value="getPath(activeSection.config, field.key)" @input="setPath(activeSection.config, field.key, $event.target.value)" class="admin-input" /><button @click="openConfigMedia(field.key)" class="admin-button">Media</button></div></div>
                            <div v-else-if="field.control === 'product_picker' && activeSection.config.source === 'manual'" class="md:col-span-2 space-y-3">
                                <span class="block text-sm text-carbon-300">{{ field.label }}</span>
                                <input v-model="productSearch" placeholder="Tìm theo tên hoặc SKU" class="admin-input" />
                                <div v-if="productSearch" class="max-h-48 overflow-y-auto rounded-xl border border-white/10 bg-carbon-950 p-2"><button v-for="product in filteredProducts.slice(0, 20)" :key="product.id" @click="addProduct(product)" class="flex w-full items-center gap-3 rounded-lg p-2 text-left hover:bg-white/5"><img v-if="product.image_url" :src="product.image_url" class="h-10 w-12 rounded object-contain" /><span class="text-sm text-white">{{ product.name }} <small class="text-carbon-500">{{ product.sku }}</small></span></button></div>
                                <draggable v-model="selectedProductModels" item-key="id" handle=".product-handle" class="space-y-2"><template #item="{ element: product }"><div class="product-handle flex cursor-move items-center gap-3 rounded-xl border border-white/5 bg-carbon-800/50 p-3"><img v-if="product.image_url" :src="product.image_url" class="h-12 w-16 rounded object-contain" /><div class="min-w-0 flex-1"><strong class="block truncate text-sm text-white">{{ product.name }}</strong><span class="text-xs text-carbon-500">{{ product.sku }} · {{ product.status }}</span></div><button @click="removeProduct(product.id)" class="text-xs text-red-300">Xóa</button></div></template></draggable>
                            </div>
                            <div v-else-if="field.control === 'post_picker' && activeSection.config.source === 'manual'" class="md:col-span-2"><span class="mb-2 block text-sm text-carbon-300">{{ field.label }}</span><div class="grid max-h-60 gap-2 overflow-y-auto rounded-xl border border-white/10 p-3 md:grid-cols-2"><label v-for="post in posts" :key="post.id" class="flex gap-2 text-sm text-carbon-300"><input type="checkbox" :checked="activeSection.config.post_ids?.includes(post.id)" @change="togglePost(post.id)" /> {{ post.title }}</label></div></div>
                        </template>
                    </div>
                </section>

                <section v-if="activeDefinition.supports_items" class="rounded-2xl border border-white/5 bg-carbon-900/60 p-5">
                    <div class="mb-4 flex items-center justify-between"><h3 class="text-lg font-bold text-white">Items</h3><button @click="addItem" class="admin-button">Thêm item</button></div>
                    <draggable v-model="activeSection.items" item-key="_key" handle=".item-handle" class="space-y-4">
                        <template #item="{ element: item, index }">
                            <div class="rounded-xl border border-white/5 bg-carbon-800/40 p-4">
                                <div class="mb-4 flex items-center justify-between"><span class="item-handle cursor-move text-sm font-bold text-carbon-400">↕ Item {{ index + 1 }}</span><div class="flex items-center gap-3"><label class="text-sm text-carbon-300"><input v-model="item.enabled" type="checkbox" /> Hiển thị</label><button @click="removeItem(index)" class="text-xs text-red-300">Xóa</button></div></div>
                                <div class="grid gap-4 md:grid-cols-2">
                                    <template v-for="field in activeDefinition.item_fields" :key="field">
                                        <label v-if="!['description', 'image'].includes(field)"><span class="mb-1 block text-sm text-carbon-300">{{ fieldLabels[field] }}</span><input :value="itemValue(item, field)" @input="setItemValue(item, field, $event.target.value)" class="admin-input" /></label>
                                        <label v-else-if="field === 'description'" class="md:col-span-2"><span class="mb-1 block text-sm text-carbon-300">{{ fieldLabels[field] }}</span><textarea :value="item.description" @input="item.description = $event.target.value" rows="3" class="admin-input"></textarea></label>
                                        <div v-else class="md:col-span-2"><span class="mb-1 block text-sm text-carbon-300">{{ fieldLabels[field] }}</span><div class="flex gap-2"><input v-model="item.image" class="admin-input" /><button @click="openItemMedia(item)" class="admin-button">Media</button></div></div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </draggable>
                </section>
            </main>
        </div>

        <Teleport to="body"><div v-if="showMediaBox" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/70" @click.self="showMediaBox = false"><div class="h-[75vh] w-[80vw] max-w-5xl overflow-y-auto rounded-2xl bg-carbon-900 p-6"><button @click="showMediaBox = false" class="mb-3 float-right text-white">Đóng</button><MediaBox @select="onMediaSelected" /></div></div></Teleport>
    </AuthenticatedLayout>
</template>

<style scoped>
.admin-input { width: 100%; border-radius: .75rem; border: 1px solid rgb(255 255 255 / .1); background: rgb(31 41 55 / .8); padding: .625rem .75rem; color: white; font-size: .875rem; }
.admin-button { flex-shrink: 0; border-radius: .75rem; border: 1px solid rgb(255 255 255 / .1); background: rgb(31 41 55); padding: .625rem 1rem; color: rgb(229 231 235); font-size: .875rem; font-weight: 600; }
</style>
