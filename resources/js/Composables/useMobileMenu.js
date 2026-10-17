import { ref } from 'vue';

// Shared between the storefront header (drawer owner) and the mobile bottom bar (trigger).
const open = ref(false);
const trigger = ref(null);

export function useMobileMenu() {
    return {
        open,
        trigger,
        show: (element) => {
            trigger.value = element || null;
            open.value = true;
        },
        hide: () => {
            open.value = false;
        },
    };
}
