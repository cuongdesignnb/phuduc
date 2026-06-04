<script setup>
import { useToast } from '@/Composables/useToast';

const { toasts, remove } = useToast();

const typeStyles = {
    success: 'bg-volt-500/15 border-volt-500/30 text-volt-300',
    error: 'bg-red-500/15 border-red-500/30 text-red-300',
    info: 'bg-blue-500/15 border-blue-500/30 text-blue-300',
    warning: 'bg-yellow-500/15 border-yellow-500/30 text-yellow-300',
};

const typeIcons = {
    success: 'M5 13l4 4L19 7',
    error: 'M6 18L18 6M6 6l12 12',
    info: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    warning: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z',
};
</script>

<template>
    <Teleport to="body">
        <div class="fixed top-4 right-4 z-[9999] flex flex-col gap-2 pointer-events-none max-w-sm w-full">
            <TransitionGroup
                enter-active-class="transition-all duration-300 ease-out"
                enter-from-class="translate-x-full opacity-0"
                enter-to-class="translate-x-0 opacity-100"
                leave-active-class="transition-all duration-200 ease-in"
                leave-from-class="translate-x-0 opacity-100"
                leave-to-class="translate-x-full opacity-0"
            >
                <div
                    v-for="toast in toasts"
                    :key="toast.id"
                    v-show="toast.visible"
                    :class="[
                        'flex items-start gap-3 px-4 py-3 rounded-xl shadow-2xl backdrop-blur-xl border text-sm font-medium pointer-events-auto cursor-pointer',
                        typeStyles[toast.type] || typeStyles.success,
                    ]"
                    @click="remove(toast.id)"
                >
                    <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" :d="typeIcons[toast.type] || typeIcons.success" />
                    </svg>
                    <span class="flex-1">{{ toast.message }}</span>
                    <button @click.stop="remove(toast.id)" class="text-current opacity-50 hover:opacity-100 shrink-0 text-lg leading-none">&times;</button>
                </div>
            </TransitionGroup>
        </div>
    </Teleport>
</template>
