<script setup>
defineProps({ open: { type: Boolean, default: false }, title: { type: String, default: 'Xác nhận thao tác' }, message: { type: String, default: '' } });
const emit = defineEmits(['confirm', 'cancel']);
</script>

<template>
    <Teleport to="body">
        <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4" role="dialog" aria-modal="true" :aria-label="title">
            <div class="w-full max-w-md border border-white/10 bg-carbon-900 p-5">
                <h2 class="text-lg font-semibold text-white">{{ title }}</h2>
                <p v-if="message" class="mt-2 text-sm text-carbon-300">{{ message }}</p>
                <div class="mt-5 flex justify-end gap-3"><button type="button" class="rounded-lg border border-white/10 px-4 py-2 text-sm text-carbon-200 focus:outline-none focus:ring-2 focus:ring-volt-400" @click="emit('cancel')">Hủy</button><button type="button" class="rounded-lg bg-volt-400 px-4 py-2 text-sm font-semibold text-carbon-950 focus:outline-none focus:ring-2 focus:ring-volt-400" @click="emit('confirm')">Xác nhận</button></div>
            </div>
        </div>
    </Teleport>
</template>
