<script setup>
import AdminEntityPicker from '@/Components/Admin/AdminEntityPicker.vue';
import AdminSelect from '@/Components/Admin/AdminSelect.vue';
import AdminTextInput from '@/Components/Admin/AdminTextInput.vue';
import { stableClientKey } from '@/Composables/useStableClientKey.js';
import draggable from 'vuedraggable';
import { computed } from 'vue';

const props = defineProps({ modelValue: { type: Array, default: () => [] }, targets: { type: Object, default: () => ({}) }, depth: { type: Number, default: 1 }, parentItems: { type: Array, default: null }, parentIndex: { type: Number, default: -1 }, nodeCount: { type: Number, default: 0 } });
const emit = defineEmits(['update:modelValue', 'remove']);
const items = computed({ get: () => props.modelValue, set: (value) => emit('update:modelValue', value) });
const targetOptions = Object.entries(props.targets).map(([key, value]) => ({ key, label: value.label }));
const targetEndpoints = { product: 'admin.menu-targets.products', post: 'admin.menu-targets.posts', category: 'admin.menu-targets.categories' };
const notify = () => emit('update:modelValue', items.value);
const newItem = () => ({ client_key: stableClientKey('menu'), title: '', url: '', model_type: 'url', model_id: null, children: [] });
const addChild = (item) => { if (props.depth >= 4 || props.nodeCount >= 100) return; item.children ??= []; item.children.push(newItem()); notify(); };
const remove = (item) => emit('remove', item);
const move = (index, amount) => { const next = index + amount; if (next < 0 || next >= items.value.length) return; [items.value[index], items.value[next]] = [items.value[next], items.value[index]]; notify(); };
const indent = (index) => { if (index < 1) return; const [item] = items.value.splice(index, 1); items.value[index - 1].children ??= []; items.value[index - 1].children.push(item); notify(); };
const outdent = (index) => { if (!props.parentItems || props.parentIndex < 0) return; const [item] = items.value.splice(index, 1); props.parentItems.splice(props.parentIndex + 1, 0, item); notify(); };
const onDrag = () => notify();
const targetEndpoint = (type) => targetEndpoints[type] || '';
const changeTarget = (item, type) => { item.model_type = type; item.model_id = null; item.url = ''; notify(); };
const chooseTarget = (item, id) => { item.model_id = id; item.url = ''; notify(); };
</script>

<template>
    <draggable v-model="items" item-key="client_key" handle=".menu-drag-handle" group="menu-items" class="space-y-3" @end="onDrag">
        <template #item="{ element: item, index }">
            <div class="border border-admin-border bg-admin-surface p-3" :class="depth > 1 ? 'ml-5' : ''">
                <div class="grid gap-2 md:grid-cols-[auto_1fr_1fr_1fr_auto] md:items-center">
                    <button type="button" class="menu-drag-handle cursor-grab px-2 text-admin-content-muted" aria-label="Kéo mục menu">::</button>
                    <AdminTextInput v-model="item.title" placeholder="Tên mục" />
                    <AdminSelect :model-value="item.model_type" :options="targetOptions" @update:model-value="changeTarget(item, $event)" />
                    <AdminTextInput v-if="item.model_type === 'url'" v-model="item.url" placeholder="URL an toàn" />
                    <AdminEntityPicker v-else :model-value="item.model_id" :endpoint="targetEndpoint(item.model_type)" placeholder="Tìm đối tượng" @update:model-value="chooseTarget(item, $event)" />
                    <div class="flex flex-wrap gap-2 text-xs text-admin-content-muted">
                        <button type="button" title="Đưa lên" aria-label="Đưa mục lên" :disabled="index === 0" @click="move(index, -1)">↑</button>
                        <button type="button" title="Đưa xuống" aria-label="Đưa mục xuống" :disabled="index === items.length - 1" @click="move(index, 1)">↓</button>
                        <button type="button" title="Thụt vào" aria-label="Thụt mục vào" :disabled="index === 0" @click="indent(index)">→</button>
                        <button type="button" title="Thụt ra" aria-label="Thụt mục ra" :disabled="!props.parentItems || props.parentIndex < 0" @click="outdent(index)">←</button>
                        <button type="button" class="text-admin-accent" @click="addChild(item)">Thêm mục con</button>
                        <button type="button" class="text-admin-danger" @click="remove(item)">Xóa</button>
                    </div>
                </div>
                <MenuItemTree v-if="item.children?.length" v-model="item.children" :targets="targets" :depth="depth + 1" :node-count="nodeCount" :parent-items="items" :parent-index="index" @remove="remove" />
            </div>
        </template>
    </draggable>
</template>

<script>
export default { name: 'MenuItemTree' };
</script>
