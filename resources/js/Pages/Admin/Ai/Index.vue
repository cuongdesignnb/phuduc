<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AdminDataCard from '@/Components/Admin/AdminDataCard.vue';
import AdminErrorSummary from '@/Components/Admin/AdminErrorSummary.vue';
import AdminFormField from '@/Components/Admin/AdminFormField.vue';
import AdminPageHeader from '@/Components/Admin/AdminPageHeader.vue';
import AdminSelect from '@/Components/Admin/AdminSelect.vue';
import AdminTextInput from '@/Components/Admin/AdminTextInput.vue';
import AdminTextarea from '@/Components/Admin/AdminTextarea.vue';
import RichContent from '@/Components/Storefront/RichContent.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({ page: { type: Object, required: true } });
const module = props.page.module;
const provider = module.settings;
const settings = useForm({
    content_api_key: '', image_api_key: '',
    content_base_url: provider.content.base_url, image_base_url: provider.image.base_url,
    content_wire_api: provider.content.wire_api, content_model: provider.content.model,
    content_max_tokens: provider.content.max_tokens, image_model: provider.image.model,
    image_quality: provider.image.quality, content_clear_api_key: false, image_clear_api_key: false,
});
const generation = ref({ type: 'article', topic: '', keywords: '', tone: 'professional', length: 'medium', full_article: true, with_images: false, image_count: 1, category_id: '' });
const result = ref(null); const busy = ref(false); const error = ref('');
const schedule = useForm({ topic: '', keywords: '', type: 'article', tone: 'professional', length: 'medium', with_images: false, image_count: 1, auto_publish: false, category_id: '', scheduled_at: '' });
const saveSettings = () => settings.post(route('admin.ai.settings.save'), { preserveScroll: true });
const generate = async () => {
    busy.value = true; error.value = ''; result.value = null;
    try {
        const payload = { ...generation.value, keywords: generation.value.keywords.split(/[,\n]+/).map((item) => item.trim()).filter(Boolean) };
        const response = await window.axios.post(route('admin.ai.content.generate'), payload);
        result.value = response.data;
    } catch (exception) {
        error.value = exception.response?.data?.message || 'Không thể sinh nội dung. Kiểm tra cấu hình AI và thử lại.';
    } finally { busy.value = false; }
};
const createDraft = (autoPublish = false) => { if (!result.value?.generation_id) return; router.post(route('admin.ai.generations.create-post', result.value.generation_id), { auto_publish: autoPublish }); };
const saveSchedule = () => schedule.post(route('admin.ai.schedules.store'), { preserveScroll: true, onSuccess: () => schedule.reset('topic', 'keywords') });
const removeSchedule = (id) => router.delete(route('admin.ai.schedules.destroy', id), { preserveScroll: true });
const options = (items) => items.map((item) => ({ key: item.key, label: item.label }));
</script>

<template>
    <Head :title="page.meta.title" />
    <AuthenticatedLayout>
        <AdminPageHeader :title="page.meta.title" />
        <div class="mt-6 space-y-6">
            <AdminDataCard title="Cấu hình provider AI">
                <p class="mb-4 text-sm text-admin-content-muted">Khóa chỉ được lưu mã hóa ở máy chủ. Để trống khóa nếu muốn giữ nguyên; không khóa nào được gửi xuống trình duyệt.</p>
                <AdminErrorSummary :errors="settings.errors" />
                <form class="grid gap-5 md:grid-cols-2" @submit.prevent="saveSettings">
                    <AdminFormField label="Content API key mới" for-id="content-api-key" :hint="provider.content.has_api_key ? `Đã cấu hình ${provider.content.key_hint}` : 'Chưa cấu hình'"><AdminTextInput id="content-api-key" v-model="settings.content_api_key" type="password" autocomplete="new-password" /></AdminFormField>
                    <AdminFormField label="Image API key mới" for-id="image-api-key" :hint="provider.image.has_api_key ? `Đã cấu hình ${provider.image.key_hint}` : 'Chưa cấu hình'"><AdminTextInput id="image-api-key" v-model="settings.image_api_key" type="password" autocomplete="new-password" /></AdminFormField>
                    <AdminFormField label="Content base URL" for-id="content-base-url" :error="settings.errors.content_base_url"><AdminTextInput id="content-base-url" v-model="settings.content_base_url" /></AdminFormField>
                    <AdminFormField label="Image base URL" for-id="image-base-url" :error="settings.errors.image_base_url"><AdminTextInput id="image-base-url" v-model="settings.image_base_url" /></AdminFormField>
                    <AdminFormField label="Content wire API" for-id="content-wire-api"><AdminSelect id="content-wire-api" v-model="settings.content_wire_api" :options="[{ key: 'chat_completions', label: 'Chat Completions' }, { key: 'responses', label: 'Responses' }]" /></AdminFormField>
                    <AdminFormField label="Content model" for-id="content-model"><AdminTextInput id="content-model" v-model="settings.content_model" /></AdminFormField>
                    <AdminFormField label="Max tokens" for-id="content-max-tokens"><AdminTextInput id="content-max-tokens" v-model.number="settings.content_max_tokens" type="number" min="256" max="12000" /></AdminFormField>
                    <AdminFormField label="Image model" for-id="image-model"><AdminTextInput id="image-model" v-model="settings.image_model" /></AdminFormField>
                    <AdminFormField label="Image quality" for-id="image-quality"><AdminSelect id="image-quality" v-model="settings.image_quality" :options="[{ key: 'low', label: 'Low' }, { key: 'medium', label: 'Medium' }, { key: 'high', label: 'High' }]" /></AdminFormField>
                    <div class="flex items-center gap-4 md:col-span-2"><label class="text-sm text-admin-content"><input v-model="settings.content_clear_api_key" type="checkbox" class="mr-2" />Xóa Content key</label><label class="text-sm text-admin-content"><input v-model="settings.image_clear_api_key" type="checkbox" class="mr-2" />Xóa Image key</label><button type="submit" class="ml-auto rounded bg-admin-accent px-5 py-2 text-sm font-semibold text-admin-page" :disabled="settings.processing">Lưu cấu hình</button></div>
                </form>
            </AdminDataCard>

            <AdminDataCard title="Sinh nội dung SEO">
                <p class="mb-4 text-sm text-admin-content-muted">AI trả về JSON có title, excerpt, HTML, meta SEO, tags và internal link được giới hạn theo URL hợp lệ của website.</p>
                <div class="grid gap-4 md:grid-cols-2">
                    <AdminFormField label="Loại nội dung" for-id="ai-type"><AdminSelect id="ai-type" v-model="generation.type" :options="options(module.types)" /></AdminFormField>
                    <AdminFormField label="Chủ đề" for-id="ai-topic"><AdminTextInput id="ai-topic" v-model="generation.topic" placeholder="Ví dụ: Cách chọn xe chở hàng điện..." /></AdminFormField>
                    <AdminFormField label="Từ khóa" for-id="ai-keywords" :hint="'Ngăn cách bằng dấu phẩy hoặc xuống dòng'"><AdminTextarea id="ai-keywords" v-model="generation.keywords" rows="3" /></AdminFormField>
                    <AdminFormField label="Danh mục bài viết" for-id="ai-category"><AdminSelect id="ai-category" v-model="generation.category_id" :options="[{ key: '', label: 'Không chọn' }, ...module.categories.map((category) => ({ key: category.id, label: category.name }))]" /></AdminFormField>
                    <AdminFormField label="Tone" for-id="ai-tone"><AdminSelect id="ai-tone" v-model="generation.tone" :options="[{ key: 'professional', label: 'Chuyên nghiệp' }, { key: 'casual', label: 'Thân thiện' }, { key: 'luxury', label: 'Cao cấp' }]" /></AdminFormField>
                    <AdminFormField label="Độ dài" for-id="ai-length"><AdminSelect id="ai-length" v-model="generation.length" :options="[{ key: 'short', label: 'Ngắn' }, { key: 'medium', label: 'Vừa' }, { key: 'long', label: 'Dài' }]" /></AdminFormField>
                </div>
                <div class="mt-4 flex flex-wrap items-center gap-5 text-sm text-admin-content"><label><input v-model="generation.with_images" type="checkbox" class="mr-2" />Sinh ảnh WebP + ALT</label><label>Ảnh <input v-model.number="generation.image_count" class="ml-2 w-16 rounded border border-admin-border bg-admin-page px-2 py-1" type="number" min="1" max="4" /></label><button type="button" class="ml-auto rounded bg-admin-accent px-5 py-2 font-semibold text-admin-page" :disabled="busy" @click="generate">{{ busy ? 'Đang sinh...' : 'Sinh nội dung' }}</button></div>
                <p v-if="error" class="mt-3 text-sm text-admin-danger" role="alert">{{ error }}</p>
                <div v-if="result?.result" class="mt-6 border-t border-admin-border pt-5">
                    <h3 class="text-lg font-semibold text-admin-content">{{ result.result.title }}</h3>
                    <p class="mt-2 text-sm text-admin-content-muted">{{ result.result.excerpt }}</p>
                    <RichContent :html="result.result.content" class="mt-4" />
                    <div v-if="result.warnings?.length" class="mt-4 text-sm text-admin-warning">{{ result.warnings.join(' ') }}</div>
                    <div class="mt-4 flex gap-3"><button type="button" class="rounded bg-admin-accent px-4 py-2 text-sm font-semibold text-admin-page" @click="createDraft(false)">Tạo bản nháp</button><button type="button" class="rounded border border-admin-border px-4 py-2 text-sm text-admin-content" @click="createDraft(true)">Tạo và đăng</button></div>
                </div>
            </AdminDataCard>

            <AdminDataCard title="Lịch sinh bài theo từ khóa">
                <form class="grid gap-4 md:grid-cols-2" @submit.prevent="saveSchedule">
                    <AdminFormField label="Chủ đề" for-id="schedule-topic" :error="schedule.errors.topic"><AdminTextInput id="schedule-topic" v-model="schedule.topic" /></AdminFormField>
                    <AdminFormField label="Từ khóa" for-id="schedule-keywords"><AdminTextarea id="schedule-keywords" v-model="schedule.keywords" rows="3" placeholder="mỗi từ khóa một dòng hoặc cách nhau bằng dấu phẩy" /></AdminFormField>
                    <AdminFormField label="Thời điểm chạy" for-id="schedule-time" hint="Worker/cron sẽ lấy lịch đến hạn mỗi phút"><AdminTextInput id="schedule-time" v-model="schedule.scheduled_at" type="datetime-local" /></AdminFormField>
                    <AdminFormField label="Danh mục" for-id="schedule-category"><AdminSelect id="schedule-category" v-model="schedule.category_id" :options="[{ key: '', label: 'Không chọn' }, ...module.categories.map((category) => ({ key: category.id, label: category.name }))]" /></AdminFormField>
                    <div class="flex items-center gap-4 text-sm text-admin-content md:col-span-2"><label><input v-model="schedule.with_images" type="checkbox" class="mr-2" />Sinh ảnh</label><label><input v-model="schedule.auto_publish" type="checkbox" class="mr-2" />Tự đăng khi hoàn tất</label><button type="submit" class="ml-auto rounded bg-admin-accent px-5 py-2 font-semibold text-admin-page" :disabled="schedule.processing">Thêm lịch</button></div>
                </form>
                <div class="mt-6 overflow-x-auto"><table class="min-w-full text-left text-sm"><thead><tr class="border-b border-admin-border text-admin-content-muted"><th class="px-2 py-2">Chủ đề</th><th class="px-2 py-2">Lịch</th><th class="px-2 py-2">Trạng thái</th><th class="px-2 py-2"></th></tr></thead><tbody><tr v-for="item in module.schedules" :key="item.id" class="border-b border-admin-border"><td class="px-2 py-2 text-admin-content">{{ item.topic }}</td><td class="px-2 py-2 text-admin-content-muted">{{ item.scheduled_at }}</td><td class="px-2 py-2 text-admin-content-muted">{{ item.status }} <span v-if="item.error_message">— {{ item.error_message }}</span></td><td class="px-2 py-2 text-right"><button v-if="item.status === 'pending'" type="button" class="text-admin-danger" @click="removeSchedule(item.id)">Xóa</button></td></tr></tbody></table><p v-if="!module.schedules.length" class="py-4 text-sm text-admin-content-muted">Chưa có lịch sinh bài.</p></div>
            </AdminDataCard>
        </div>
    </AuthenticatedLayout>
</template>
