<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import MediaBox from '@/Components/MediaBox.vue';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    sections: Array,
});

const labels = {
    pageTitle: 'N\u1ed9i dung trang ch\u1ee7',
    pageHint: 'Qu\u1ea3n l\u00fd c\u00e1c block l\u1eb7p tr\u00ean trang ch\u1ee7.',
    items: 'Items',
    empty: 'Ch\u01b0a c\u00f3 item n\u00e0o.',
    save: 'L\u01b0u n\u1ed9i dung',
    saving: '\u0110ang l\u01b0u...',
    addItem: 'Th\u00eam item',
    remove: 'X\u00f3a',
    active: 'Hi\u1ec3n th\u1ecb',
    enabled: 'B\u1eadt section',
    sectionTitle: 'Ti\u00eau \u0111\u1ec1 section',
    sectionSubtitle: 'Ph\u1ee5 \u0111\u1ec1 section',
    sectionDescription: 'M\u00f4 t\u1ea3 section',
    title: 'Ti\u00eau \u0111\u1ec1',
    subtitle: 'Ph\u1ee5 \u0111\u1ec1 / vai tr\u00f2',
    description: 'M\u00f4 t\u1ea3 / n\u1ed9i dung',
    image: '\u1ea2nh',
    icon: 'Icon',
    url: 'Li\u00ean k\u1ebft',
    tone: 'Tone / style',
    avatarText: 'Ch\u1eef avatar',
    media: 'Media',
    moveUp: 'L\u00ean',
    moveDown: 'Xu\u1ed1ng',
};

const sectionMeta = {
    category_cards: { label: 'Danh m\u1ee5c', hint: 'Card danh m\u1ee5c ngay d\u01b0\u1edbi hero.' },
    benefits: { label: 'Cam k\u1ebft', hint: 'D\u00f2ng icon cam k\u1ebft d\u1ecbch v\u1ee5.' },
    industry_solutions: { label: 'Gi\u1ea3i ph\u00e1p theo ng\u00e0nh', hint: 'Danh s\u00e1ch ng\u00e0nh \u1ee9ng d\u1ee5ng.' },
    testimonials: { label: 'Kh\u00e1ch h\u00e0ng n\u00f3i g\u00ec', hint: 'Tr\u00edch d\u1eabn, t\u00ean v\u00e0 vai tr\u00f2 kh\u00e1ch h\u00e0ng.' },
    partners: { label: '\u0110\u1ed1i t\u00e1c', hint: 'Logo ho\u1eb7c t\u00ean \u0111\u1ed1i t\u00e1c.' },
    consultation_steps: { label: 'B\u01b0\u1edbc t\u01b0 v\u1ea5n', hint: 'Quy tr\u00ecnh t\u01b0 v\u1ea5n 3-4 b\u01b0\u1edbc.' },
};

const defaultSections = Object.keys(sectionMeta).map((key, index) => ({
    key,
    title: sectionMeta[key].label,
    subtitle: '',
    description: '',
    is_enabled: true,
    sort_order: index,
    settings_json: {},
    items: [],
}));

const cloneItem = (item = {}) => ({
    id: item.id || null,
    title: item.title || '',
    subtitle: item.subtitle || '',
    description: item.description || '',
    image: item.image || '',
    icon: item.icon || '',
    url: item.url || '',
    metadata_json: item.metadata_json || {},
    is_active: item.is_active ?? true,
    sort_order: item.sort_order ?? 0,
});

const cloneSection = (section = {}) => ({
    key: section.key,
    title: section.title || sectionMeta[section.key]?.label || section.key,
    subtitle: section.subtitle || '',
    description: section.description || '',
    is_enabled: section.is_enabled ?? true,
    sort_order: section.sort_order ?? 0,
    settings_json: section.settings_json || {},
    items: (section.items || []).map(cloneItem).sort((a, b) => a.sort_order - b.sort_order),
});

const sections = ref(defaultSections.map((section) => {
    const existing = (props.sections || []).find((item) => item.key === section.key);
    return cloneSection(existing || section);
}));

const activeKey = ref(sections.value[0]?.key || 'category_cards');
const isSaving = ref(false);
const showMediaBox = ref(false);
const mediaTarget = ref(null);

const activeSection = computed(() => sections.value.find((section) => section.key === activeKey.value));

const addItem = () => {
    activeSection.value.items.push(cloneItem({
        title: '',
        is_active: true,
        sort_order: activeSection.value.items.length,
        metadata_json: {},
    }));
};

const removeItem = (index) => {
    activeSection.value.items.splice(index, 1);
    resequence(activeSection.value.items);
};

const moveItem = (index, direction) => {
    const next = index + direction;
    if (next < 0 || next >= activeSection.value.items.length) return;
    const items = activeSection.value.items;
    [items[index], items[next]] = [items[next], items[index]];
    resequence(items);
};

const resequence = (items) => {
    items.forEach((item, index) => {
        item.sort_order = index;
    });
};

const openMedia = (sectionKey, itemIndex) => {
    mediaTarget.value = { sectionKey, itemIndex };
    showMediaBox.value = true;
};

const onMediaSelected = (media) => {
    const targetSection = sections.value.find((section) => section.key === mediaTarget.value?.sectionKey);
    const targetItem = targetSection?.items[mediaTarget.value?.itemIndex];
    if (targetItem) {
        targetItem.image = media.file_path || '';
    }
    showMediaBox.value = false;
};

const normalizedSections = () => sections.value.map((section, sectionIndex) => ({
    ...section,
    sort_order: sectionIndex,
    items: section.items.map((item, itemIndex) => ({
        ...item,
        sort_order: itemIndex,
        metadata_json: {
            ...(item.metadata_json || {}),
            tone: item.metadata_json?.tone || '',
            avatar_text: item.metadata_json?.avatar_text || '',
        },
    })),
}));

const save = () => {
    if (isSaving.value) return;
    isSaving.value = true;

    router.post(route('admin.home-content.save'), {
        sections: normalizedSections(),
    }, {
        preserveScroll: true,
        onFinish: () => {
            isSaving.value = false;
        },
    });
};
</script>

<template>
    <Head :title="labels.pageTitle" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-display font-bold text-white">{{ labels.pageTitle }}</h2>
                    <p class="mt-1 text-sm text-carbon-400">{{ labels.pageHint }}</p>
                </div>
                <button @click="save" :disabled="isSaving" class="rounded-xl bg-volt-500 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-volt-500/20 transition hover:bg-volt-600 disabled:opacity-50">
                    {{ isSaving ? labels.saving : labels.save }}
                </button>
            </div>
        </template>

        <div class="mx-auto max-w-7xl px-6 py-6">
            <div class="mb-6 flex flex-wrap gap-2 rounded-2xl border border-white/5 bg-carbon-900/50 p-2">
                <button
                    v-for="section in sections"
                    :key="section.key"
                    @click="activeKey = section.key"
                    class="rounded-xl px-4 py-2 text-sm font-semibold transition"
                    :class="activeKey === section.key ? 'bg-volt-500/15 text-volt-400' : 'text-carbon-400 hover:bg-white/5 hover:text-white'"
                >
                    {{ sectionMeta[section.key]?.label || section.key }}
                </button>
            </div>

            <div v-if="activeSection" class="space-y-5">
                <div class="rounded-2xl border border-white/5 bg-carbon-900/50 p-5">
                    <div class="mb-4 flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-semibold text-white">{{ sectionMeta[activeSection.key]?.label }}</h3>
                            <p class="text-sm text-carbon-500">{{ sectionMeta[activeSection.key]?.hint }}</p>
                        </div>
                        <label class="inline-flex items-center gap-2 text-sm font-medium text-carbon-300">
                            <input v-model="activeSection.is_enabled" type="checkbox" class="rounded border-white/10 bg-carbon-800 text-volt-500 focus:ring-volt-500" />
                            {{ labels.enabled }}
                        </label>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <label class="block">
                            <span class="mb-1.5 block text-sm font-medium text-carbon-300">{{ labels.sectionTitle }}</span>
                            <input v-model="activeSection.title" type="text" class="w-full rounded-xl border border-white/10 bg-carbon-800 px-3 py-2 text-sm text-white focus:border-volt-500/60 focus:ring-volt-500/20" />
                        </label>
                        <label class="block">
                            <span class="mb-1.5 block text-sm font-medium text-carbon-300">{{ labels.sectionSubtitle }}</span>
                            <input v-model="activeSection.subtitle" type="text" class="w-full rounded-xl border border-white/10 bg-carbon-800 px-3 py-2 text-sm text-white focus:border-volt-500/60 focus:ring-volt-500/20" />
                        </label>
                        <label class="block md:col-span-2">
                            <span class="mb-1.5 block text-sm font-medium text-carbon-300">{{ labels.sectionDescription }}</span>
                            <textarea v-model="activeSection.description" rows="2" class="w-full rounded-xl border border-white/10 bg-carbon-800 px-3 py-2 text-sm text-white focus:border-volt-500/60 focus:ring-volt-500/20"></textarea>
                        </label>
                    </div>
                </div>

                <div class="rounded-2xl border border-white/5 bg-carbon-900/50 p-5">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-white">{{ labels.items }}</h3>
                        <button @click="addItem" class="rounded-xl border border-white/10 bg-carbon-800 px-4 py-2 text-sm font-semibold text-carbon-200 transition hover:bg-carbon-700">{{ labels.addItem }}</button>
                    </div>

                    <div class="space-y-4">
                        <div v-for="(item, index) in activeSection.items" :key="item.id || index" class="rounded-2xl border border-white/5 bg-carbon-800/40 p-4">
                            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                                <label class="inline-flex items-center gap-2 text-sm font-medium text-carbon-300">
                                    <input v-model="item.is_active" type="checkbox" class="rounded border-white/10 bg-carbon-800 text-volt-500 focus:ring-volt-500" />
                                    {{ labels.active }}
                                </label>
                                <div class="flex gap-2">
                                    <button @click="moveItem(index, -1)" class="rounded-lg border border-white/10 px-3 py-1.5 text-xs text-carbon-300 hover:bg-white/5">{{ labels.moveUp }}</button>
                                    <button @click="moveItem(index, 1)" class="rounded-lg border border-white/10 px-3 py-1.5 text-xs text-carbon-300 hover:bg-white/5">{{ labels.moveDown }}</button>
                                    <button @click="removeItem(index)" class="rounded-lg border border-red-500/20 px-3 py-1.5 text-xs text-red-300 hover:bg-red-500/10">{{ labels.remove }}</button>
                                </div>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <label class="block">
                                    <span class="mb-1.5 block text-sm font-medium text-carbon-300">{{ labels.title }}</span>
                                    <input v-model="item.title" type="text" class="w-full rounded-xl border border-white/10 bg-carbon-900 px-3 py-2 text-sm text-white focus:border-volt-500/60 focus:ring-volt-500/20" />
                                </label>
                                <label class="block">
                                    <span class="mb-1.5 block text-sm font-medium text-carbon-300">{{ labels.subtitle }}</span>
                                    <input v-model="item.subtitle" type="text" class="w-full rounded-xl border border-white/10 bg-carbon-900 px-3 py-2 text-sm text-white focus:border-volt-500/60 focus:ring-volt-500/20" />
                                </label>
                                <label class="block md:col-span-2">
                                    <span class="mb-1.5 block text-sm font-medium text-carbon-300">{{ labels.description }}</span>
                                    <textarea v-model="item.description" rows="3" class="w-full rounded-xl border border-white/10 bg-carbon-900 px-3 py-2 text-sm text-white focus:border-volt-500/60 focus:ring-volt-500/20"></textarea>
                                </label>
                                <label class="block">
                                    <span class="mb-1.5 block text-sm font-medium text-carbon-300">{{ labels.icon }}</span>
                                    <input v-model="item.icon" type="text" placeholder="shield, award, truck, cart..." class="w-full rounded-xl border border-white/10 bg-carbon-900 px-3 py-2 text-sm text-white focus:border-volt-500/60 focus:ring-volt-500/20" />
                                </label>
                                <label class="block">
                                    <span class="mb-1.5 block text-sm font-medium text-carbon-300">{{ labels.url }}</span>
                                    <input v-model="item.url" type="text" placeholder="/san-pham" class="w-full rounded-xl border border-white/10 bg-carbon-900 px-3 py-2 text-sm text-white focus:border-volt-500/60 focus:ring-volt-500/20" />
                                </label>
                                <label class="block">
                                    <span class="mb-1.5 block text-sm font-medium text-carbon-300">{{ labels.tone }}</span>
                                    <input v-model="item.metadata_json.tone" type="text" placeholder="warehouse, factory, blue, green..." class="w-full rounded-xl border border-white/10 bg-carbon-900 px-3 py-2 text-sm text-white focus:border-volt-500/60 focus:ring-volt-500/20" />
                                </label>
                                <label class="block">
                                    <span class="mb-1.5 block text-sm font-medium text-carbon-300">{{ labels.avatarText }}</span>
                                    <input v-model="item.metadata_json.avatar_text" type="text" maxlength="3" class="w-full rounded-xl border border-white/10 bg-carbon-900 px-3 py-2 text-sm text-white focus:border-volt-500/60 focus:ring-volt-500/20" />
                                </label>
                                <div class="md:col-span-2">
                                    <span class="mb-1.5 block text-sm font-medium text-carbon-300">{{ labels.image }}</span>
                                    <div class="flex gap-2">
                                        <input v-model="item.image" type="text" placeholder="media/file.webp" class="flex-1 rounded-xl border border-white/10 bg-carbon-900 px-3 py-2 text-sm text-white focus:border-volt-500/60 focus:ring-volt-500/20" />
                                        <button @click="openMedia(activeSection.key, index)" class="rounded-xl border border-white/10 bg-carbon-900 px-4 py-2 text-sm font-semibold text-carbon-200 transition hover:bg-carbon-700">{{ labels.media }}</button>
                                    </div>
                                    <img v-if="item.image" :src="'/storage/' + item.image.replace(/^\/?storage\//, '')" class="mt-3 h-20 w-auto rounded-lg border border-white/10 object-cover" />
                                </div>
                            </div>
                        </div>

                        <div v-if="!activeSection.items.length" class="rounded-xl border border-dashed border-white/10 py-10 text-center text-sm text-carbon-500">
                            {{ labels.empty }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <Teleport to="body">
            <div v-if="showMediaBox" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/60 backdrop-blur-sm" @click.self="showMediaBox = false">
                <div class="h-[75vh] w-[80vw] max-w-5xl overflow-y-auto rounded-2xl border border-white/10 bg-carbon-900 p-6 shadow-2xl">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-white">{{ labels.media }}</h3>
                        <button @click="showMediaBox = false" class="rounded-lg p-1.5 text-carbon-400 transition hover:bg-red-500/10 hover:text-red-400">x</button>
                    </div>
                    <MediaBox @select="onMediaSelected" />
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>
