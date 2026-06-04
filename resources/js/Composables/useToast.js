import { reactive } from 'vue';

const toasts = reactive([]);
let nextId = 0;

export function useToast() {
    const add = (message, type = 'success', duration = 4000) => {
        const id = ++nextId;
        toasts.push({ id, message, type, visible: true });
        if (duration > 0) {
            setTimeout(() => remove(id), duration);
        }
        return id;
    };

    const remove = (id) => {
        const idx = toasts.findIndex(t => t.id === id);
        if (idx !== -1) {
            toasts[idx].visible = false;
            setTimeout(() => {
                const i = toasts.findIndex(t => t.id === id);
                if (i !== -1) toasts.splice(i, 1);
            }, 300);
        }
    };

    const success = (msg, duration) => add(msg, 'success', duration);
    const error = (msg, duration) => add(msg, 'error', duration);
    const info = (msg, duration) => add(msg, 'info', duration);
    const warning = (msg, duration) => add(msg, 'warning', duration);

    return { toasts, add, remove, success, error, info, warning };
}
