<script setup>
import AdminSelect from '@/Components/Admin/AdminSelect.vue';
import AdminTextInput from '@/Components/Admin/AdminTextInput.vue';
import { stableClientKey } from '@/Composables/useStableClientKey.js';
import draggable from 'vuedraggable';
import { ref } from 'vue';

const props = defineProps({ modelValue: { type: Array, default: () => [] }, targets: { type: Object, default: () => ({}) }, depth: { type: Number, default: 1 }, parentItems: { type: Array, default: null }, parentIndex: { type: Number, default: -1 } });
const emit = defineEmits(['update:modelValue', 'remove']);
const items = ref(props.modelValue);
const targetOptions = Object.entries(props.targets).map(([key, value]) => ({ key, label: value.label }));
const notify = () => emit('update:modelValue', items.value);
const newItem = () => ({ client_key: stableClientKey('menu'), title: '', url: '', model_type: 'url', model_id: null, children: [] });
const addChild = (item) => { item.children ??= []; item.children.push(newItem()); notify(); };
const remove = (item) => emit('remove', item);
const move = (index, amount) => { const next = index + amount; if (next < 0 || next >= items.value.length) return; [items.value[index], items.value[next]] = [items.value[next], items.value[index]]; notify(); };
const indent = (index) => { if (index < 1) return; const [item] = items.value.splice(index, 1); items.value[index - 1].children ??= []; items.value[index - 1].children.push(item); notify(); };
const outdent = (index) => { if (!props.parentItems || props.parentIndex < 0) return; const [item] = items.value.splice(index, 1); props.parentItems.splice(props.parentIndex + 1, 0, item); emit('update:modelValue', items.value); };
const onDrag = () => notify();
</script>

<template>
    <draggable v-model="items" item-key="client_key" handle=".menu-drag-handle" group="menu-items" class="space-y-3" @end="onDrag">
        <template #item="{ element: item, index }">
            <div class="border border-admin-border bg-admin-surface p-3" :class="depth > 1 ? 'ml-5' : ''">
                <div class="grid gap-2 md:grid-cols-[auto_1fr_1fr_11rem_auto] md:items-center">
                    <button type="button" class="menu-drag-handle cursor-grab px-2 text-admin-content-muted" aria-label="Drag menu item">::</button>
                    <AdminTextInput v-model="item.title" placeholder="Item label" />
                    <AdminTextInput v-model="item.url" placeholder="Safe URL" />
                    <AdminSelect v-model="item.model_type" :options="targetOptions" />
                    <div class="flex flex-wrap gap-2 text-xs text-admin-content-muted">
                        <button type="button" title="Move item up" aria-label="Move item up" @click="move(index, -1)">↑</button>
                        <button type="button" title="Move item down" aria-label="Move item down" @click="move(index, 1)">↓</button>
                        <button type="button" title="Indent item" aria-label="Indent item" @click="indent(index)">→</button>
                        <button type="button" title="Outdent item" aria-label="Outdent item" @click="outdent(index)">←</button>
                        <button type="button" class="text-admin-accent" @click="addChild(item)">Add child</button>
                        <button type="button" class="text-admin-danger" @click="remove(item)">Remove</button>
                    </div>
                </div>
                <MenuItemTree v-if="item.children?.length" v-model="item.children" :targets="targets" :depth="depth + 1" :parent-items="items" :parent-index="index" @remove="remove" />
            </div>
        </template>
    </draggable>
</template>

<script>
export default { name: 'MenuItemTree' };
</script>
