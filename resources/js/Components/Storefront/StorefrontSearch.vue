<script setup>
import { Link, router } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';

const props = defineProps({
    modelValue: { type: String, default: '' },
    id: { type: String, required: true },
    compact: { type: Boolean, default: false },
});
const emit = defineEmits(['update:modelValue', 'submit']);
const value = ref(props.modelValue);
const root = ref(null);
const suggestions = ref([]);
const activeIndex = ref(-1);
const isOpen = ref(false);
const isLoading = ref(false);
const hasSearched = ref(false);
let requestController;
let debounceTimer;

const trimmedValue = computed(() => value.value.trim());
const canSearch = computed(() => trimmedValue.value.length >= 2);
const suggestionPanelId = computed(() => `${props.id}-suggestions`);
const hasPanel = computed(() => isOpen.value && canSearch.value && (isLoading.value || hasSearched.value));

const closeSuggestions = () => {
    isOpen.value = false;
    activeIndex.value = -1;
};

const requestSuggestions = async (query) => {
    requestController?.abort();
    requestController = new AbortController();
    isLoading.value = true;
    hasSearched.value = false;

    try {
        const url = new URL(route('products.suggestions'), window.location.origin);
        url.searchParams.set('q', query);

        const response = await fetch(url, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            signal: requestController.signal,
        });

        if (!response.ok) {
            throw new Error('Unable to load product suggestions.');
        }

        const payload = await response.json();
        if (query === trimmedValue.value) {
            suggestions.value = Array.isArray(payload.items) ? payload.items : [];
            hasSearched.value = true;
        }
    } catch (error) {
        if (error.name !== 'AbortError' && query === trimmedValue.value) {
            suggestions.value = [];
            hasSearched.value = true;
        }
    } finally {
        if (query === trimmedValue.value) {
            isLoading.value = false;
        }
    }
};

const scheduleSuggestions = (query) => {
    clearTimeout(debounceTimer);
    requestController?.abort();
    activeIndex.value = -1;

    if (query.length < 2) {
        suggestions.value = [];
        hasSearched.value = false;
        isLoading.value = false;
        closeSuggestions();
        return;
    }

    isOpen.value = true;
    isLoading.value = true;
    hasSearched.value = false;
    debounceTimer = window.setTimeout(() => requestSuggestions(query), 220);
};

const chooseSuggestion = (item) => {
    closeSuggestions();
    router.visit(item.url);
};

const moveActiveSuggestion = (step) => {
    if (!suggestions.value.length) return;

    isOpen.value = true;
    activeIndex.value = (activeIndex.value + step + suggestions.value.length) % suggestions.value.length;
};

const onInputKeydown = (event) => {
    if (event.key === 'ArrowDown') {
        event.preventDefault();
        moveActiveSuggestion(1);
    } else if (event.key === 'ArrowUp') {
        event.preventDefault();
        moveActiveSuggestion(-1);
    } else if (event.key === 'Escape') {
        event.preventDefault();
        closeSuggestions();
    } else if (event.key === 'Enter') {
        event.preventDefault();
        const activeItem = suggestions.value[activeIndex.value];
        if (activeItem) {
            chooseSuggestion(activeItem);
        } else {
            submit();
        }
    }
};

const submit = () => {
    const keyword = trimmedValue.value;
    if (!keyword) return;

    closeSuggestions();
    emit('submit', keyword);
};

const onClickOutside = (event) => {
    if (root.value && !root.value.contains(event.target)) {
        closeSuggestions();
    }
};

watch(() => props.modelValue, (nextValue) => {
    if (nextValue !== value.value) {
        value.value = nextValue;
    }
});

watch(value, (nextValue) => {
    emit('update:modelValue', nextValue);
    scheduleSuggestions(nextValue.trim());
});

onMounted(() => {
    document.addEventListener('pointerdown', onClickOutside);
});
onBeforeUnmount(() => {
    clearTimeout(debounceTimer);
    requestController?.abort();
    document.removeEventListener('pointerdown', onClickOutside);
});
</script>

<template>
    <div ref="root" class="relative w-full">
        <form class="flex w-full overflow-hidden rounded-lg border border-line bg-surface-card focus-within:border-brand-border" role="search" @submit.prevent="submit">
            <label :for="id" class="sr-only">Tìm kiếm sản phẩm</label>
            <input
                :id="id"
                v-model="value"
                type="search"
                name="search"
                autocomplete="off"
                role="combobox"
                aria-autocomplete="list"
                :aria-expanded="hasPanel"
                :aria-controls="suggestionPanelId"
                :aria-activedescendant="activeIndex >= 0 ? `${id}-suggestion-${activeIndex}` : undefined"
                class="min-w-0 flex-1 border-0 bg-transparent px-4 text-sm text-content-primary placeholder:text-content-muted focus:ring-0"
                :class="compact ? 'h-11' : 'h-12'"
                placeholder="Tìm sản phẩm theo tên hoặc mã..."
                @focus="canSearch && (isOpen = true)"
                @keydown="onInputKeydown"
            >
            <button type="submit" class="grid min-w-12 place-items-center bg-brand text-brand-contrast transition hover:bg-brand-hover" aria-label="Tìm kiếm sản phẩm">
                <svg class="h-5 w-5" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" />
                </svg>
            </button>
        </form>

        <div
            v-if="hasPanel"
            :id="suggestionPanelId"
            class="absolute inset-x-0 top-full z-[80] mt-2 overflow-hidden rounded-xl border border-line bg-surface-elevated p-1"
            role="listbox"
            style="box-shadow: var(--ds-shadow-dropdown)"
        >
            <p v-if="isLoading" class="px-3 py-3 text-sm text-content-muted" role="status">Đang tìm sản phẩm…</p>
            <template v-else-if="suggestions.length">
                <Link
                    v-for="(item, index) in suggestions"
                    :id="`${id}-suggestion-${index}`"
                    :key="item.id"
                    :href="item.url"
                    role="option"
                    :aria-selected="activeIndex === index"
                    class="flex items-center gap-3 rounded-lg px-2 py-2 text-left transition"
                    :class="activeIndex === index ? 'bg-brand-soft text-content-primary' : 'text-content-primary hover:bg-surface-muted'"
                    @mouseenter="activeIndex = index"
                    @click="closeSuggestions"
                >
                    <img v-if="item.image_url" :src="item.image_url" :alt="item.name" class="h-11 w-11 shrink-0 rounded-lg border border-line object-cover">
                    <span v-else class="grid h-11 w-11 shrink-0 place-items-center rounded-lg bg-surface-muted text-xs font-bold text-content-muted" aria-hidden="true">PD</span>
                    <span class="min-w-0 flex-1">
                        <span class="block truncate text-sm font-semibold">{{ item.name }}</span>
                        <span v-if="item.sku" class="mt-0.5 block truncate text-xs text-content-muted">Mã: {{ item.sku }}</span>
                    </span>
                    <span class="shrink-0 text-sm font-bold text-brand-text">{{ item.price_display }}</span>
                </Link>
                <button type="button" class="flex min-h-10 w-full items-center rounded-lg px-3 text-left text-sm font-semibold text-brand-text transition hover:bg-brand-soft" @click="submit">
                    Xem tất cả kết quả cho “{{ trimmedValue }}”
                </button>
            </template>
            <p v-else class="px-3 py-3 text-sm text-content-muted">Không tìm thấy sản phẩm phù hợp.</p>
        </div>
    </div>
</template>
