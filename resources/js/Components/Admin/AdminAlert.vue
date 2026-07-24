<script setup>
import { computed } from 'vue';

const props = defineProps({
    message: { type: String, default: '' },
    title: { type: String, default: '' },
    tone: { type: String, default: 'info' },
    dismissible: { type: Boolean, default: false },
    closeLabel: { type: String, default: 'Đóng thông báo' },
});
const emit = defineEmits(['close']);
const role = computed(() => props.tone === 'error' ? 'alert' : 'status');
const live = computed(() => props.tone === 'error' ? 'assertive' : 'polite');
</script>

<template>
    <div v-if="message || title" :role="role" :aria-live="live" :class="tone === 'error' ? 'border-admin-danger/40 text-admin-danger' : tone === 'success' ? 'border-admin-success/40 text-admin-success' : 'border-admin-accent/40 text-admin-content'" class="flex items-start justify-between gap-4 border px-4 py-3 text-sm">
        <div><p v-if="title" class="font-semibold">{{ title }}</p><p v-if="message" :class="title ? 'mt-1' : ''">{{ message }}</p></div>
        <button v-if="dismissible" type="button" class="shrink-0 rounded p-1 text-admin-content-muted hover:text-admin-content focus:outline-none focus:ring-2 focus:ring-admin-focus" :aria-label="closeLabel" @click="emit('close')">×</button>
    </div>
</template>
