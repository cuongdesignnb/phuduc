<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AdminDataCard from '@/Components/Admin/AdminDataCard.vue';
import AdminErrorSummary from '@/Components/Admin/AdminErrorSummary.vue';
import AdminFormField from '@/Components/Admin/AdminFormField.vue';
import AdminMediaPicker from '@/Components/Admin/AdminMediaPicker.vue';
import AdminPageHeader from '@/Components/Admin/AdminPageHeader.vue';
import AdminSelect from '@/Components/Admin/AdminSelect.vue';
import AdminTextInput from '@/Components/Admin/AdminTextInput.vue';
import AdminTextarea from '@/Components/Admin/AdminTextarea.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({ page: { type: Object, required: true } });
const module = props.page.module; const settings = Object.values(module.groups).flat();
const form = useForm({ settings: settings.map((setting) => ({ key: setting.key, value: setting.value ?? '', media_id: setting.media_id || null })), version: module.version });
const picker = ref(false); const selected = ref(null); const selectedSetting = computed(() => selected.value ? form.settings.find((item) => item.key === selected.value) : null);
const settingValue = (key) => form.settings.find((item) => item.key === key);
const openPicker = (key) => { selected.value = key; picker.value = true; };
const chooseMedia = (media) => { if (selectedSetting.value) selectedSetting.value.media_id = media.id; picker.value = false; };
const clearMedia = () => { if (selectedSetting.value) { selectedSetting.value.media_id = null; selectedSetting.value.value = ''; } };
const save = () => form.post(route('admin.settings.save'), { onSuccess: (page) => { const version = page.props.page?.module?.version; if (version) form.version = version; form.defaults({ settings: form.settings, version: form.version }); form.reset(); } });
</script>

<template>
    <Head :title="page.meta.title" /><AuthenticatedLayout><AdminPageHeader :title="page.meta.title" /><div class="mt-6 space-y-6"><AdminErrorSummary :errors="form.errors" /><AdminDataCard v-for="(group, key) in module.groups" :key="key" :title="module.group_labels?.[key] || key"><div class="grid gap-5 md:grid-cols-2"><AdminFormField v-for="setting in group" :key="setting.key" :label="setting.label" :for-id="`setting-${setting.key}`" :hint="setting.description"><AdminTextarea v-if="setting.type === 'textarea'" :id="`setting-${setting.key}`" v-model="settingValue(setting.key).value" rows="5" /><AdminSelect v-else-if="setting.type === 'font'" :id="`setting-${setting.key}`" v-model="settingValue(setting.key).value" :options="module.font_options.map(option => ({ key: option.name, label: option.name }))" /><div v-else-if="setting.type === 'image'" class="flex items-center gap-3"><button type="button" class="rounded border border-admin-border px-3 py-2 text-sm text-admin-content" @click="openPicker(setting.key)">Chọn tệp</button><button v-if="settingValue(setting.key).media_id" type="button" class="border border-admin-border px-3 py-2 text-sm text-admin-danger" @click="selected = setting.key; clearMedia()">Xóa ảnh</button><span class="text-sm text-admin-content-muted">{{ settingValue(setting.key).media_id ? `Tệp #${settingValue(setting.key).media_id}` : 'Chưa chọn' }}</span></div><AdminTextInput v-else :id="`setting-${setting.key}`" v-model="settingValue(setting.key).value" /></AdminFormField></div></AdminDataCard><div class="flex justify-end"><button type="button" :disabled="form.processing" class="rounded bg-admin-accent px-5 py-2 text-sm font-semibold text-admin-page" @click="save">{{ form.processing ? 'Đang lưu...' : 'Lưu cài đặt' }}</button></div></div><AdminMediaPicker :open="picker" :selected-id="selectedSetting?.media_id" media-type="image" @close="picker = false" @select="chooseMedia" /></AuthenticatedLayout>
</template>
