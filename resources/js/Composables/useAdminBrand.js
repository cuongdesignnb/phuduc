import { computed, unref } from 'vue';

export function useAdminBrand(site) {
    const siteName = computed(() => {
        const name = unref(site)?.name?.trim();

        return name || 'Quản trị';
    });
    const initial = computed(() => siteName.value.slice(0, 1).toUpperCase() || 'A');

    return { siteName, initial };
}
