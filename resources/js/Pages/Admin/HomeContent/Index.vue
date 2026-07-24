<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AdminDataCard from '@/Components/Admin/AdminDataCard.vue';
import AdminErrorSummary from '@/Components/Admin/AdminErrorSummary.vue';
import AdminFormField from '@/Components/Admin/AdminFormField.vue';
import AdminMediaPicker from '@/Components/Admin/AdminMediaPicker.vue';
import AdminPageHeader from '@/Components/Admin/AdminPageHeader.vue';
import AdminSelect from '@/Components/Admin/AdminSelect.vue';
import AdminTextInput from '@/Components/Admin/AdminTextInput.vue';
import AdminTextarea from '@/Components/Admin/AdminTextarea.vue';
import { Head, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import draggable from 'vuedraggable';
import { computed, onMounted, ref, watch } from 'vue';
import { stableClientKey } from '@/Composables/useStableClientKey.js';

const props = defineProps({ page: { type: Object, required: true } });
const module = props.page.module;
const clone = (value) => JSON.parse(JSON.stringify(value));
const sections = ref(clone(module.sections).map((section, sectionIndex) => ({
    ...section,
    sort_order: section.sort_order ?? sectionIndex,
    items: (section.items || []).map((item) => ({ ...item, _key: item.id ? `item-${item.id}` : stableClientKey('home-item') })),
})));
const activeKey = ref(sections.value[0]?.key || null);
const pickerOpen = ref(false);
const pickerTarget = ref(null);
const productSearch = ref('');
const postSearch = ref('');
const productOptions = ref([]);
const postOptions = ref([]);
const form = useForm({ sections: [], version: module.version });
const activeSection = computed(() => sections.value.find((section) => section.key === activeKey.value));
const activeDefinition = computed(() => module.registry[activeKey.value] || {});
const errors = computed(() => form.errors);

const getPath = (object, path) => path.split('.').reduce((value, key) => value?.[key], object);
const setPath = (object, path, value) => {
    const parts = path.split('.');
    let target = object;
    parts.forEach((part, index) => {
        if (index === parts.length - 1) target[part] = value;
        else { target[part] ??= {}; target = target[part]; }
    });
};
const mediaLabel = (id) => id ? `Tệp #${id}` : 'Chưa chọn tệp';
const configMediaLabel = (config, key) => mediaLabel(getPath(config, `${key}_media_id`));
const productLabel = (id) => productOptions.value.find((product) => product.id === id)?.name || `Product #${id}`;
const fieldLabel = (field) => ({ title: 'Tiêu đề', subtitle: 'Tiêu đề phụ', description: 'Mô tả', image: 'Ảnh', icon: 'Biểu tượng', url: 'URL', tone: 'Tông màu', avatar_text: 'Chữ thay thế' }[field] || field);
const sourceLabel = (value) => ({ manual: 'Chọn thủ công', latest: 'Mới nhất' }[value] || value);
const selectedIds = (key) => activeSection.value?.config?.[key] || [];
const filteredProducts = computed(() => productOptions.value.filter((product) => !selectedIds('product_ids').includes(product.id)));
const filteredPosts = computed(() => postOptions.value);
const addProduct = (product) => { activeSection.value.config.product_ids = [...selectedIds('product_ids'), product.id]; };
const removeProduct = (id) => { activeSection.value.config.product_ids = selectedIds('product_ids').filter((value) => value !== id); };
const togglePost = (id) => { const ids = selectedIds('post_ids'); activeSection.value.config.post_ids = ids.includes(id) ? ids.filter((value) => value !== id) : [...ids, id]; };
const addItem = () => activeSection.value.items.push({ id: null, _key: stableClientKey('home-item'), title: '', subtitle: '', description: '', image: null, media_id: null, icon: '', url: '', metadata: {}, enabled: true, sort_order: activeSection.value.items.length });
const removeItem = (index) => activeSection.value.items.splice(index, 1);
const itemValue = (item, field) => ['tone', 'avatar_text'].includes(field) ? item.metadata?.[field] : item[field];
const setItemValue = (item, field, value) => { if (['tone', 'avatar_text'].includes(field)) { item.metadata ??= {}; item.metadata[field] = value; } else item[field] = value; };
const openConfigMedia = (path) => { pickerTarget.value = { kind: 'config', path }; pickerOpen.value = true; };
const openItemMedia = (item) => { pickerTarget.value = { kind: 'item', item }; pickerOpen.value = true; };
const chooseMedia = (media) => { if (pickerTarget.value?.kind === 'config') setPath(activeSection.value.config, `${pickerTarget.value.path}_media_id`, media.id); if (pickerTarget.value?.kind === 'item') pickerTarget.value.item.media_id = media.id; pickerOpen.value = false; };
const clearConfigMedia = (path) => { setPath(activeSection.value.config, `${path}_media_id`, null); setPath(activeSection.value.config, path, null); };
const clearItemMedia = (item) => { item.media_id = null; item.image = null; };
const itemPayload = (item, definition) => {
    const payload = { id: item.id || null, enabled: Boolean(item.enabled), sort_order: item.sort_order, metadata: {} };
    (definition.item_fields || []).forEach((field) => { if (['tone', 'avatar_text'].includes(field)) payload.metadata[field] = item.metadata?.[field] ?? null; else if (field === 'image') { payload.image = item.image || null; payload.media_id = item.media_id || null; } else payload[field] = item[field] ?? null; });
    return payload;
};
const save = () => {
    sections.value.forEach((section, sectionIndex) => { section.sort_order = sectionIndex; section.items.forEach((item, itemIndex) => { item.sort_order = itemIndex; }); });
    form.sections = sections.value.map((section) => ({ ...section, items: section.items.map((item) => itemPayload(item, module.registry[section.key] || {})) }));
    form.post(route('admin.home-content.save'), { preserveScroll: true, onSuccess: (page) => { if (page.props.page?.module?.version) form.version = page.props.page.module.version; form.defaults({ sections: form.sections, version: form.version }); form.reset(); } });
};
const loadLookup = async (kind, search, ids = []) => { const response = await axios.get(route(`admin.home-content.${kind}`), { params: { search: search || undefined, ids, limit: 20 } }); if (kind === 'products') productOptions.value = response.data.items || response.data.data || []; else postOptions.value = response.data.items || response.data.data || []; };
watch(productSearch, (value) => loadLookup('products', value));
watch(postSearch, (value) => loadLookup('posts', value));
onMounted(() => { const productIds = [...new Set(sections.value.flatMap((section) => section.config?.product_ids || []))]; const postIds = [...new Set(sections.value.flatMap((section) => section.config?.post_ids || []))]; loadLookup('products', '', productIds); loadLookup('posts', '', postIds); });
</script>

<template>
    <Head :title="page.meta.title" />
    <AuthenticatedLayout>
        <AdminPageHeader :title="page.meta.title" description="Quản lý các khu vực, hình ảnh và thứ tự hiển thị trên trang chủ.">
            <button type="button" class="rounded-lg bg-admin-accent px-4 py-2 text-sm font-semibold text-admin-page disabled:opacity-60" :disabled="form.processing" @click="save">{{ form.processing ? 'Đang lưu...' : 'Lưu nội dung' }}</button>
        </AdminPageHeader>
        <div class="mt-6 grid gap-6 lg:grid-cols-[15rem_1fr]">
            <AdminDataCard title="Khu vực">
                <div class="space-y-1">
                    <button v-for="section in sections" :key="section.key" type="button" class="flex w-full items-center justify-between border px-3 py-2 text-left text-sm" :class="activeKey === section.key ? 'border-admin-accent bg-admin-accent/10 text-admin-content' : 'border-admin-border text-admin-content-muted'" @click="activeKey = section.key">
                        <span>{{ module.registry[section.key]?.label || section.key }}</span><span aria-hidden="true">{{ section.enabled ? 'Bật' : 'Tắt' }}</span>
                    </button>
                </div>
            </AdminDataCard>
            <main v-if="activeSection" class="space-y-6">
                <AdminErrorSummary :errors="errors" />
                <AdminDataCard :title="activeDefinition.label || activeSection.key">
                    <div class="grid gap-4 md:grid-cols-2">
                        <AdminFormField v-if="activeDefinition.heading_fields?.includes('title')" label="Tiêu đề" for-id="section-title"><AdminTextInput id="section-title" v-model="activeSection.heading.title" /></AdminFormField>
                        <AdminFormField v-if="activeDefinition.heading_fields?.includes('subtitle')" label="Tiêu đề phụ" for-id="section-subtitle"><AdminTextInput id="section-subtitle" v-model="activeSection.heading.subtitle" /></AdminFormField>
                        <AdminFormField v-if="activeDefinition.heading_fields?.includes('description')" label="Mô tả" for-id="section-description"><AdminTextarea id="section-description" v-model="activeSection.heading.description" /></AdminFormField>
                        <AdminFormField label="Biến thể" for-id="section-variant"><AdminSelect id="section-variant" v-model="activeSection.variant" :options="(activeDefinition.allowed_variants || []).map((value) => ({ key: value, label: sourceLabel(value) }))" /></AdminFormField>
                        <label class="flex items-center gap-2 text-sm text-admin-content"><input v-model="activeSection.enabled" type="checkbox" /> Đang bật</label>
                    </div>
                </AdminDataCard>
                <AdminDataCard v-if="activeDefinition.config_fields?.length" title="Cấu hình khu vực">
                    <div class="grid gap-4 md:grid-cols-2">
                        <template v-for="field in activeDefinition.config_fields" :key="field.key">
                            <AdminFormField v-if="['text', 'number'].includes(field.control)" :label="field.label" :for-id="`config-${field.key}`"><AdminTextInput :id="`config-${field.key}`" :type="field.control" :model-value="getPath(activeSection.config, field.key)" @update:model-value="setPath(activeSection.config, field.key, field.control === 'number' ? Number($event) : $event)" /></AdminFormField>
                            <AdminFormField v-else-if="field.control === 'select'" :label="field.label" :for-id="`config-${field.key}`"><AdminSelect :id="`config-${field.key}`" :model-value="getPath(activeSection.config, field.key)" :options="(field.options || []).map((value) => ({ key: value, label: sourceLabel(value) }))" @update:model-value="setPath(activeSection.config, field.key, $event)" /></AdminFormField>
                            <AdminFormField v-else-if="field.control === 'media'" :label="field.label" :for-id="`config-${field.key}`"><div class="flex items-center gap-3"><span class="text-sm text-admin-content-muted">{{ configMediaLabel(activeSection.config, field.key) }}</span><button type="button" class="rounded-lg border border-admin-border px-3 py-2 text-sm text-admin-content" @click="openConfigMedia(field.key)">Chọn tệp</button><button v-if="getPath(activeSection.config, `${field.key}_media_id`)" type="button" class="border border-admin-border px-3 py-2 text-sm text-admin-danger" @click="clearConfigMedia(field.key)">Xóa ảnh</button></div></AdminFormField>
                            <div v-else-if="field.control === 'product_picker' && activeSection.config.source === 'manual'" class="md:col-span-2"><label class="mb-2 block text-sm font-medium text-admin-content">{{ field.label }}</label><AdminTextInput v-model="productSearch" placeholder="Tìm sản phẩm" /><div class="mt-2 space-y-1"><button v-for="product in filteredProducts" :key="product.id" type="button" class="block w-full border border-admin-border px-3 py-2 text-left text-sm text-admin-content" @click="addProduct(product)">{{ product.name }} <span class="text-admin-content-muted">{{ product.sku }}</span></button></div><div class="mt-3 space-y-1"><div v-for="id in selectedIds('product_ids')" :key="id" class="flex items-center justify-between border border-admin-border px-3 py-2 text-sm text-admin-content"><span>{{ productLabel(id) }}</span><button type="button" class="text-admin-danger" @click="removeProduct(id)">Xóa</button></div></div></div>
                            <div v-else-if="field.control === 'post_picker' && activeSection.config.source === 'manual'" class="md:col-span-2"><label class="mb-2 block text-sm font-medium text-admin-content">{{ field.label }}</label><AdminTextInput v-model="postSearch" placeholder="Tìm bài viết" /><label v-for="post in filteredPosts" :key="post.id" class="mt-2 flex items-center gap-2 text-sm text-admin-content"><input type="checkbox" :checked="selectedIds('post_ids').includes(post.id)" @change="togglePost(post.id)" />{{ post.title }}</label></div>
                        </template>
                    </div>
                </AdminDataCard>
                <AdminDataCard v-if="activeDefinition.supports_items" title="Danh sách mục">
                    <div class="mb-4 flex justify-end"><button type="button" class="rounded-lg border border-admin-border px-3 py-2 text-sm text-admin-content" @click="addItem">Thêm mục</button></div>
                    <draggable v-model="activeSection.items" item-key="_key" class="space-y-4">
                        <template #item="{ element: item, index }">
                            <div class="border border-admin-border p-4"><div class="mb-3 flex items-center justify-between text-sm text-admin-content"><span>Mục {{ index + 1 }}</span><button type="button" class="text-admin-danger" @click="removeItem(index)">Xóa</button></div><div class="grid gap-4 md:grid-cols-2"><template v-for="field in activeDefinition.item_fields" :key="field"><AdminFormField v-if="!['description', 'image'].includes(field)" :label="fieldLabel(field)" :for-id="`item-${item._key}-${field}`"><AdminTextInput :id="`item-${item._key}-${field}`" :model-value="itemValue(item, field)" @update:model-value="setItemValue(item, field, $event)" /></AdminFormField><AdminFormField v-else-if="field === 'description'" :label="fieldLabel(field)" :for-id="`item-${item._key}-description`"><AdminTextarea :id="`item-${item._key}-description`" :model-value="item.description" @update:model-value="item.description = $event" /></AdminFormField><AdminFormField v-else :label="fieldLabel(field)" :for-id="`item-${item._key}-image`"><div class="flex items-center gap-3"><span class="text-sm text-admin-content-muted">{{ mediaLabel(item.media_id) }}</span><button type="button" class="rounded-lg border border-admin-border px-3 py-2 text-sm text-admin-content" @click="openItemMedia(item)">Chọn tệp</button><button v-if="item.media_id" type="button" class="border border-admin-border px-3 py-2 text-sm text-admin-danger" @click="clearItemMedia(item)">Xóa ảnh</button></div></AdminFormField></template></div></div>
                        </template>
                    </draggable>
                </AdminDataCard>
            </main>
        </div>
        <AdminMediaPicker :open="pickerOpen" media-type="image" @close="pickerOpen = false" @select="chooseMedia" />
    </AuthenticatedLayout>
</template>
