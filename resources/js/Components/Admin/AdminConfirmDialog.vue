<script setup>
import { computed, ref, toRef } from 'vue';
import { useModalFocus } from '@/Composables/useModalFocus.js';

const props = defineProps({
    open: { type: Boolean, default: false },
    title: { type: String, default: 'Xác nhận thao tác' },
    message: { type: String, default: '' },
    confirmLabel: { type: String, default: 'Xác nhận' },
    cancelLabel: { type: String, default: 'Hủy' },
    processing: { type: Boolean, default: false },
    danger: { type: Boolean, default: false },
    initialFocus: { type: String, default: '' },
});
const emit = defineEmits(['confirm', 'cancel']);
const dialog = ref(null);
const instanceId = `admin-confirm-${Math.random().toString(36).slice(2)}`;
const titleId = `${instanceId}-title`;
const messageId = `${instanceId}-message`;
const focusMode = computed(() => props.initialFocus === 'confirm' ? 'last' : 'first');
const { onKeydown } = useModalFocus({
    open: toRef(props, 'open'),
    container: dialog,
    initialFocus: focusMode.value,
    onEscape: () => {
        if (!props.processing) emit('cancel');
    },
});

const cancel = () => {
    if (!props.processing) emit('cancel');
};
const confirm = () => {
    if (!props.processing) emit('confirm');
};
</script>

<template>
    <Teleport to="body">
        <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4" role="dialog" aria-modal="true" :aria-labelledby="titleId" :aria-describedby="message ? messageId : undefined">
            <div ref="dialog" tabindex="-1" class="w-full max-w-md border border-admin-border bg-admin-surface p-5 focus:outline-none" @keydown="onKeydown">
                <h2 :id="titleId" class="text-lg font-semibold text-admin-content">{{ title }}</h2>
                <p v-if="message" :id="messageId" class="mt-2 text-sm text-admin-content-muted">{{ message }}</p>
                <div v-if="$slots.default" class="mt-4"><slot /></div>
                <div class="mt-5 flex justify-end gap-3">
                    <button type="button" :disabled="processing" class="rounded-lg border border-admin-border px-4 py-2 text-sm text-admin-content-muted focus:outline-none focus:ring-2 focus:ring-admin-focus disabled:cursor-not-allowed disabled:opacity-50" @click="cancel">{{ cancelLabel }}</button>
                    <button type="button" :disabled="processing" :class="danger ? 'bg-admin-danger text-white' : 'bg-admin-accent text-admin-page'" class="rounded-lg px-4 py-2 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-admin-focus disabled:cursor-not-allowed disabled:opacity-50" @click="confirm">{{ processing ? 'Đang xử lý...' : confirmLabel }}</button>
                </div>
            </div>
        </div>
    </Teleport>
</template>
