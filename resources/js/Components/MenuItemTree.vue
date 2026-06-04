<script setup>
import { ref } from 'vue';
import draggable from 'vuedraggable';

const props = defineProps({
    modelValue: Array,
    depth: { type: Number, default: 0 },
});

const emit = defineEmits(['update:modelValue']);

const items = ref(props.modelValue);

const updateItems = () => {
    emit('update:modelValue', items.value);
};

const editingId = ref(null);

const toggleEdit = (item) => {
    editingId.value = editingId.value === item.id ? null : item.id;
};

const removeItem = (index) => {
    if (confirm('Xóa mục này và các mục con?')) {
        items.value.splice(index, 1);
        updateItems();
    }
};

const addChild = (item) => {
    if (!item.children) item.children = [];
    item.children.push({
        id: Date.now(),
        title: 'Mục con mới',
        url: '',
        model_type: '',
        model_id: null,
        children: [],
    });
    updateItems();
};

const onDragEnd = () => {
    updateItems();
};
</script>

<template>
    <draggable
        v-model="items"
        group="menu-items"
        item-key="id"
        handle=".drag-handle"
        ghost-class="opacity-30"
        @end="onDragEnd"
        :class="depth > 0 ? 'ml-8 mt-2 border-l-2 border-gray-200 dark:border-gray-600 pl-4' : ''"
    >
        <template #item="{ element, index }">
            <div class="mb-2">
                <div class="flex items-center gap-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 p-3 group">
                    <!-- Drag handle -->
                    <div class="drag-handle cursor-grab text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/>
                        </svg>
                    </div>

                    <!-- Title -->
                    <span class="flex-1 text-sm font-medium text-gray-800 dark:text-gray-200">
                        {{ element.title }}
                        <span v-if="element.url" class="ml-2 text-xs text-gray-400">{{ element.url }}</span>
                    </span>

                    <!-- Action buttons -->
                    <button
                        @click="addChild(element)"
                        class="text-xs text-blue-500 hover:text-blue-700 opacity-0 group-hover:opacity-100 transition"
                        title="Thêm con"
                    >
                        + Con
                    </button>
                    <button
                        @click="toggleEdit(element)"
                        class="text-xs text-industrial-500 hover:text-industrial-700 opacity-0 group-hover:opacity-100 transition"
                    >
                        Sửa
                    </button>
                    <button
                        @click="removeItem(index)"
                        class="text-xs text-red-500 hover:text-red-700 opacity-0 group-hover:opacity-100 transition"
                    >
                        Xóa
                    </button>
                </div>

                <!-- Inline edit form -->
                <div v-if="editingId === element.id" class="mt-2 ml-7 p-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg space-y-2">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Tiêu đề</label>
                            <input
                                v-model="element.title"
                                type="text"
                                class="w-full text-sm rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-industrial-500 focus:ring-industrial-500"
                                @input="updateItems"
                            />
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">URL</label>
                            <input
                                v-model="element.url"
                                type="text"
                                placeholder="/san-pham, https://..."
                                class="w-full text-sm rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-industrial-500 focus:ring-industrial-500"
                                @input="updateItems"
                            />
                        </div>
                    </div>
                    <button @click="editingId = null" class="text-xs text-gray-500 hover:text-gray-700">Đóng</button>
                </div>

                <!-- Recursive children -->
                <MenuItemTree
                    v-if="element.children && element.children.length"
                    v-model="element.children"
                    :depth="depth + 1"
                    @update:modelValue="updateItems"
                />
            </div>
        </template>
    </draggable>
</template>

<script>
export default {
    name: 'MenuItemTree',
};
</script>
