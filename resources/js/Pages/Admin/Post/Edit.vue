<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AdvancedTextEditor from '@/Components/AdvancedTextEditor.vue';
import MediaBox from '@/Components/MediaBox.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({ post: Object, categories: Array });
const showMediaPicker = ref(false);

const form = useForm({
    title: props.post?.title || '',
    slug: props.post?.slug || '',
    post_category_id: props.post?.post_category_id || '',
    summary: props.post?.summary || '',
    content: props.post?.content || '',
    featured_image: props.post?.featured_image || '',
    status: props.post?.status || 'draft',
});

const save = () => {
    if (props.post) {
        form.put(route('admin.posts.update', props.post.id));
    } else {
        form.post(route('admin.posts.store'));
    }
};

const selectFeaturedImage = (media) => {
    form.featured_image = media.file_path;
    showMediaPicker.value = false;
};
</script>

<template>
    <Head :title="post ? 'Sửa bài viết' : 'Viết bài mới'" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-display font-bold text-white">{{ post ? 'Sửa bài viết' : 'Viết bài mới' }}</h2>
        </template>
        <div class="py-6">
            <div class="mx-auto max-w-7xl px-6">
                <form @submit.prevent="save" class="space-y-6">
                    <div class="rounded-2xl border border-white/5 bg-carbon-900/50 backdrop-blur-sm p-6 space-y-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-carbon-300 mb-1.5">Tiêu đề *</label>
                                <input v-model="form.title" type="text" required class="w-full rounded-xl border border-white/10 bg-carbon-800 text-white placeholder-carbon-500 py-2.5 px-4 text-sm focus:border-volt-500/50 focus:ring-1 focus:ring-volt-500/20 transition" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-carbon-300 mb-1.5">Slug</label>
                                <input v-model="form.slug" type="text" placeholder="Tự sinh" class="w-full rounded-xl border border-white/10 bg-carbon-800 text-white placeholder-carbon-500 py-2.5 px-4 text-sm focus:border-volt-500/50 focus:ring-1 focus:ring-volt-500/20 transition" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-carbon-300 mb-1.5">Danh mục</label>
                                <select v-model="form.post_category_id" class="w-full rounded-xl border border-white/10 bg-carbon-800 text-white py-2.5 px-4 text-sm focus:border-volt-500/50 focus:ring-1 focus:ring-volt-500/20 transition">
                                    <option value="">— Không có —</option>
                                    <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-carbon-300 mb-1.5">Trạng thái</label>
                                <select v-model="form.status" class="w-full rounded-xl border border-white/10 bg-carbon-800 text-white py-2.5 px-4 text-sm focus:border-volt-500/50 focus:ring-1 focus:ring-volt-500/20 transition">
                                    <option value="draft">Nháp</option>
                                    <option value="published">Đăng ngay</option>
                                </select>
                            </div>
                        </div>

                        <!-- Featured Image via MediaBox -->
                        <div>
                            <label class="block text-sm font-medium text-carbon-300 mb-1.5">Ảnh đại diện</label>
                            <div class="flex items-center gap-4">
                                <img v-if="form.featured_image" :src="'/storage/' + form.featured_image" class="w-32 h-20 object-cover rounded-xl border border-white/10" />
                                <button type="button" @click="showMediaPicker = !showMediaPicker" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-white/10 bg-carbon-800 text-carbon-300 text-sm hover:bg-carbon-700 hover:text-white transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    {{ form.featured_image ? 'Đổi ảnh' : 'Chọn từ Media Library' }}
                                </button>
                                <button v-if="form.featured_image" type="button" @click="form.featured_image = ''" class="text-sm text-red-400 hover:text-red-300 transition-colors">Xóa</button>
                            </div>
                            <div v-if="showMediaPicker" class="mt-4 rounded-xl border border-white/10 bg-carbon-800/50 p-4">
                                <MediaBox :on-select="selectFeaturedImage" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-carbon-300 mb-1.5">Tóm tắt</label>
                            <textarea v-model="form.summary" rows="3" class="w-full rounded-xl border border-white/10 bg-carbon-800 text-white placeholder-carbon-500 py-2.5 px-4 text-sm focus:border-volt-500/50 focus:ring-1 focus:ring-volt-500/20 transition"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-carbon-300 mb-1.5">Nội dung</label>
                            <AdvancedTextEditor v-model="form.content" />
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" :disabled="form.processing" class="inline-flex items-center gap-2 rounded-xl bg-volt-500 px-6 py-2.5 text-sm font-semibold text-white hover:bg-volt-600 disabled:opacity-50 transition-all shadow-lg shadow-volt-500/20">
                            {{ post ? 'Cập nhật' : 'Đăng bài' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
