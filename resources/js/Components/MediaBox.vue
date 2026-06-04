<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
    onSelect: { type: Function, default: null },
});

const emit = defineEmits(['select', 'close']);

const medias = ref([]);
const isUploading = ref(false);
const uploadProgress = ref(0);
const totalFiles = ref(0);
const uploadedFiles = ref(0);
const isDragging = ref(false);
const currentPage = ref(1);
const lastPage = ref(1);
const fileInput = ref(null);

const fetchMedia = async (page = 1) => {
    try {
        const res = await axios.get('/admin/media', { params: { page } });
        if (page === 1) {
            medias.value = res.data.data;
        } else {
            medias.value.push(...res.data.data);
        }
        currentPage.value = res.data.current_page;
        lastPage.value = res.data.last_page;
    } catch (e) {
        console.error('Fetch media error', e);
    }
};

const handleUpload = async (files) => {
    if (!files || !files.length) return;
    isUploading.value = true;
    totalFiles.value = files.length;
    uploadedFiles.value = 0;
    uploadProgress.value = 0;

    for (let file of files) {
        const formData = new FormData();
        formData.append('file', file);
        try {
            await axios.post('/admin/media', formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
                onUploadProgress: (e) => {
                    if (e.total) {
                        uploadProgress.value = Math.round((e.loaded / e.total) * 100);
                    }
                },
            });
            uploadedFiles.value++;
        } catch (error) {
            console.error('Upload error', error);
        }
    }
    await fetchMedia();
    isUploading.value = false;
    uploadProgress.value = 0;
    if (fileInput.value) fileInput.value.value = '';
};

const onFileChange = (e) => handleUpload(e.target.files);

const onDragOver = (e) => {
    e.preventDefault();
    isDragging.value = true;
};

const onDragLeave = () => {
    isDragging.value = false;
};

const onDrop = (e) => {
    e.preventDefault();
    isDragging.value = false;
    handleUpload(e.dataTransfer.files);
};

const selectMedia = (media) => {
    emit('select', media);
    if (props.onSelect) props.onSelect(media);
};

const deleteMedia = async (id) => {
    if (!confirm('Bạn có chắc chắn muốn xóa ảnh này?')) return;
    try {
        await axios.delete(`/admin/media/${id}`);
        medias.value = medias.value.filter(m => m.id !== id);
    } catch (e) {
        console.error(e);
    }
};

const loadMore = () => {
    if (currentPage.value < lastPage.value) {
        fetchMedia(currentPage.value + 1);
    }
};

const formatSize = (bytes) => {
    if (!bytes) return '';
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
};

onMounted(() => fetchMedia());
</script>

<template>
    <div class="rounded-xl">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-display font-semibold text-white">Media Library</h2>
            <div>
                <input type="file" ref="fileInput" @change="onFileChange" multiple accept="image/*,video/*,application/pdf" class="hidden" />
                <button @click="fileInput.click()" :disabled="isUploading"
                  class="inline-flex items-center gap-1.5 rounded-xl bg-volt-500 px-4 py-2 text-sm font-semibold text-white hover:bg-volt-600 transition-all shadow-lg shadow-volt-500/20 disabled:opacity-50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                    Tải lên
                </button>
            </div>
        </div>

        <!-- Upload progress -->
        <div v-if="isUploading" class="mb-4 rounded-xl border border-volt-500/20 bg-carbon-800/50 p-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm text-carbon-300">
                    Đang tải {{ uploadedFiles + 1 }}/{{ totalFiles }}...
                </span>
                <span class="text-sm font-mono text-volt-400">{{ uploadProgress }}%</span>
            </div>
            <div class="w-full h-2 bg-carbon-700 rounded-full overflow-hidden">
                <div class="h-full bg-volt-500 rounded-full transition-all duration-300" :style="{ width: uploadProgress + '%' }"></div>
            </div>
        </div>

        <!-- Drag & drop zone -->
        <div
            @dragover="onDragOver"
            @dragleave="onDragLeave"
            @drop="onDrop"
            class="mb-4 border-2 border-dashed rounded-xl p-6 text-center transition-all duration-200 cursor-pointer"
            :class="isDragging ? 'border-volt-500 bg-volt-500/10 text-volt-400' : 'border-white/10 text-carbon-500 hover:border-white/20 hover:text-carbon-400'"
            @click="fileInput.click()"
        >
            <svg class="w-8 h-8 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
            <p class="text-sm">Kéo thả file vào đây hoặc nhấn để chọn</p>
            <p class="text-xs mt-1 opacity-60">Hỗ trợ: ảnh, video, PDF — tối đa 10MB</p>
        </div>

        <!-- Media grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
            <div v-for="media in medias" :key="media.id"
                 class="relative group rounded-xl border border-white/10 overflow-hidden cursor-pointer hover:border-volt-500/50 transition-all bg-carbon-800/50"
                 @click="selectMedia(media)">

                <div class="aspect-square flex items-center justify-center">
                    <img v-if="media.mime_type && media.mime_type.startsWith('image/')" :src="`/storage/${media.file_path}`" class="object-cover w-full h-full" loading="lazy" />
                    <div v-else class="text-center p-2">
                        <svg class="w-8 h-8 mx-auto mb-1 text-carbon-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <p class="text-[10px] font-medium text-carbon-400 truncate">{{ media.file_name }}</p>
                    </div>
                </div>

                <!-- File info overlay -->
                <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent p-2 opacity-0 group-hover:opacity-100 transition-all">
                    <p class="text-[10px] text-white truncate">{{ media.file_name }}</p>
                    <p class="text-[10px] text-carbon-400">{{ formatSize(media.size) }}</p>
                </div>

                <button @click.stop="deleteMedia(media.id)" class="absolute top-1.5 right-1.5 bg-red-500/80 backdrop-blur-sm text-white p-1 rounded-lg opacity-0 group-hover:opacity-100 transition-all hover:bg-red-500">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        </div>

        <!-- Empty state -->
        <div v-if="!medias.length && !isUploading" class="text-center py-12 text-carbon-500 border-2 border-dashed border-white/10 rounded-xl">
            Chưa có file nào. Kéo thả hoặc nhấn "Tải lên" để bắt đầu.
        </div>

        <!-- Load more -->
        <div v-if="currentPage < lastPage" class="mt-4 text-center">
            <button @click="loadMore" class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-carbon-800 px-5 py-2 text-sm font-medium text-carbon-300 hover:bg-carbon-700 hover:text-white transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                Tải thêm
            </button>
        </div>
    </div>
</template>
