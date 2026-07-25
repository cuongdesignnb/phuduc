<script setup>
import { ref, watch } from 'vue';
import axios from 'axios';
import AdminTextInput from '@/Components/Admin/AdminTextInput.vue';

const props = defineProps({ modelValue: { type: [Number, String], default: null }, endpoint: { type: String, required: true }, placeholder: { type: String, default: 'Chọn đối tượng' } });
const emit = defineEmits(['update:modelValue']);
const search = ref('');
const items = ref([]);
const loading = ref(false);
const selected = ref(null);
let timer;

const load = async (ids = []) => {
    loading.value = true;
    try {
        const response = await axios.get(route(props.endpoint), { params: { search: search.value || undefined, ids: ids.length ? ids : undefined, limit: 20 } });
        items.value = response.data.items || [];
        selected.value = items.value.find((item) => Number(item.id) === Number(props.modelValue)) || selected.value;
    } finally {
        loading.value = false;
    }
};
const choose = (item) => { selected.value = item; emit('update:modelValue', item.id); };
const clear = () => { selected.value = null; emit('update:modelValue', null); };
watch(() => props.modelValue, (value) => { if (value) load([value]); else selected.value = null; }, { immediate: true });
watch(search, () => { clearTimeout(timer); timer = setTimeout(() => load(), 200); });
</script>

<template>
    <div class="space-y-2">
        <div class="flex gap-2"><AdminTextInput v-model="search" :placeholder="placeholder" /><button v-if="modelValue" type="button" class="shrink-0 border border-admin-border px-2 text-xs text-admin-danger" title="Xóa lựa chọn" aria-label="Xóa lựa chọn" @click="clear">×</button></div>
        <p v-if="selected" class="text-xs text-admin-content-muted">{{ selected.label }}<span v-if="selected.status"> · {{ selected.status }}</span></p>
        <div v-if="search && !loading" class="max-h-32 overflow-y-auto border border-admin-border bg-admin-surface p-1">
            <button v-for="item in items" :key="item.id" type="button" class="block w-full px-2 py-1 text-left text-xs text-admin-content hover:bg-admin-surface-muted" @click="choose(item)">{{ item.label }}<span v-if="item.status" class="text-admin-content-muted"> · {{ item.status }}</span></button>
            <p v-if="!items.length" class="px-2 py-1 text-xs text-admin-content-muted">Không tìm thấy.</p>
        </div>
    </div>
</template>
