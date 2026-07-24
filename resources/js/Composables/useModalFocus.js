import { nextTick, onBeforeUnmount, ref, watch } from 'vue';

const FOCUSABLE_SELECTOR = 'button:not([disabled]), a[href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

export function useModalFocus({ open, container, initialFocus = 'first', onEscape, returnFocus = true }) {
    const previousActive = ref(null);
    const bodyOverflow = ref('');

    const focusable = () => Array.from(container.value?.querySelectorAll(FOCUSABLE_SELECTOR) || [])
        .filter((element) => element.offsetWidth > 0 || element.offsetHeight > 0 || element === document.activeElement);

    const focusInitial = () => {
        const elements = focusable();
        const target = initialFocus === 'last' ? elements.at(-1) : elements[0];

        (target || container.value)?.focus();
    };

    const onKeydown = (event) => {
        if (event.key === 'Escape') {
            if (!event.defaultPrevented) onEscape?.();
            return;
        }

        if (event.key !== 'Tab') return;

        const elements = focusable();
        if (!elements.length) return;

        const first = elements[0];
        const last = elements[elements.length - 1];
        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    };

    const restore = () => {
        document.body.style.overflow = bodyOverflow.value;
        if (returnFocus && previousActive.value?.isConnected) previousActive.value.focus();
        previousActive.value = null;
    };

    const activate = async () => {
        previousActive.value = document.activeElement;
        bodyOverflow.value = document.body.style.overflow;
        document.body.style.overflow = 'hidden';
        await nextTick();
        focusInitial();
    };

    watch(open, (isOpen) => {
        if (isOpen) activate();
        else if (previousActive.value) restore();
    }, { flush: 'post', immediate: true });

    onBeforeUnmount(() => {
        if (previousActive.value) restore();
    });

    return { onKeydown, restore };
}
