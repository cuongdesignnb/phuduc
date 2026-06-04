<script setup>
import { ref, watch, onMounted, nextTick } from 'vue';
import { QuillEditor } from '@vueup/vue-quill';
import '@vueup/vue-quill/dist/vue-quill.snow.css';
import MediaBox from '@/Components/MediaBox.vue';

const props = defineProps({
    modelValue: { type: String, default: '' },
    height: { type: Number, default: 300 },
});
const emit = defineEmits(['update:modelValue']);
const showMediaBox = ref(false);
const quillRef = ref(null);

const toolbarOptions = [
    [{ header: [1, 2, 3, 4, false] }],
    ['bold', 'italic', 'underline', 'strike'],
    [{ color: [] }, { background: [] }],
    [{ align: [] }],
    [{ list: 'ordered' }, { list: 'bullet' }],
    ['blockquote', 'code-block'],
    ['link', 'image', 'video'],
    ['clean'],
];

const editorOptions = {
    theme: 'snow',
    modules: {
        toolbar: {
            container: toolbarOptions,
            handlers: {
                image: function () {
                    showMediaBox.value = true;
                },
            },
        },
    },
    placeholder: 'Nhập nội dung...',
};

const handleMediaSelect = (media) => {
    showMediaBox.value = false;
    const quill = quillRef.value?.getQuill();
    if (quill) {
        const range = quill.getSelection(true);
        const url = media.file_path ? `/storage/${media.file_path}` : (media.url || '');
        quill.insertEmbed(range.index, 'image', url);
        quill.setSelection(range.index + 1);
    }
};

const onContentChange = ({ html }) => {
    emit('update:modelValue', html);
};
</script>

<template>
  <div class="quill-editor-wrap relative">
    <QuillEditor
        ref="quillRef"
        :content="modelValue"
        content-type="html"
        :options="editorOptions"
        :style="{ height: height + 'px' }"
        @update:content="emit('update:modelValue', $event)"
    />

    <!-- MediaBox Modal -->
    <Teleport to="body">
      <div v-if="showMediaBox" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/60 backdrop-blur-sm" @click.self="showMediaBox = false">
        <div class="bg-carbon-900 border border-white/10 p-6 rounded-2xl w-[80vw] max-w-5xl h-[75vh] overflow-y-auto shadow-2xl">
          <div class="flex justify-between items-center mb-4">
              <h3 class="text-lg font-display font-semibold text-white">Media Library</h3>
              <button @click="showMediaBox = false" class="rounded-lg p-1.5 text-carbon-400 hover:bg-red-500/10 hover:text-red-400 transition">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
              </button>
          </div>
          <MediaBox :onSelect="handleMediaSelect" />
        </div>
      </div>
    </Teleport>
  </div>
</template>

<style scoped>
.quill-editor-wrap :deep(.ql-toolbar) {
    background: rgb(30 30 30 / 0.8);
    border-color: rgb(255 255 255 / 0.1);
    border-radius: 0.75rem 0.75rem 0 0;
}
.quill-editor-wrap :deep(.ql-toolbar .ql-stroke) {
    stroke: rgb(180 180 180);
}
.quill-editor-wrap :deep(.ql-toolbar .ql-fill) {
    fill: rgb(180 180 180);
}
.quill-editor-wrap :deep(.ql-toolbar .ql-picker-label) {
    color: rgb(180 180 180);
}
.quill-editor-wrap :deep(.ql-toolbar button:hover .ql-stroke),
.quill-editor-wrap :deep(.ql-toolbar .ql-active .ql-stroke) {
    stroke: #09DE52;
}
.quill-editor-wrap :deep(.ql-toolbar button:hover .ql-fill),
.quill-editor-wrap :deep(.ql-toolbar .ql-active .ql-fill) {
    fill: #09DE52;
}
.quill-editor-wrap :deep(.ql-toolbar button:hover),
.quill-editor-wrap :deep(.ql-toolbar .ql-active) {
    color: #09DE52;
}
.quill-editor-wrap :deep(.ql-toolbar .ql-picker-label:hover),
.quill-editor-wrap :deep(.ql-toolbar .ql-picker-label.ql-active) {
    color: #09DE52;
}
.quill-editor-wrap :deep(.ql-toolbar .ql-picker-label:hover .ql-stroke),
.quill-editor-wrap :deep(.ql-toolbar .ql-picker-label.ql-active .ql-stroke) {
    stroke: #09DE52;
}
.quill-editor-wrap :deep(.ql-container) {
    background: rgb(23 23 23 / 0.8);
    border-color: rgb(255 255 255 / 0.1);
    border-radius: 0 0 0.75rem 0.75rem;
    color: #e5e5e5;
    font-size: 0.875rem;
}
.quill-editor-wrap :deep(.ql-editor.ql-blank::before) {
    color: rgb(115 115 115);
    font-style: normal;
}
.quill-editor-wrap :deep(.ql-editor) {
    min-height: 150px;
}
.quill-editor-wrap :deep(.ql-snow .ql-picker-options) {
    background: rgb(30 30 30);
    border-color: rgb(255 255 255 / 0.1);
    border-radius: 0.5rem;
}
.quill-editor-wrap :deep(.ql-snow .ql-picker-item) {
    color: rgb(180 180 180);
}
.quill-editor-wrap :deep(.ql-snow .ql-picker-item:hover) {
    color: #09DE52;
}
.quill-editor-wrap :deep(.ql-snow .ql-tooltip) {
    background: rgb(30 30 30);
    border-color: rgb(255 255 255 / 0.1);
    color: rgb(180 180 180);
    border-radius: 0.5rem;
    box-shadow: 0 10px 25px rgb(0 0 0 / 0.5);
}
.quill-editor-wrap :deep(.ql-snow .ql-tooltip input[type=text]) {
    background: rgb(23 23 23);
    border-color: rgb(255 255 255 / 0.1);
    color: white;
    border-radius: 0.375rem;
}
.quill-editor-wrap :deep(.ql-snow .ql-tooltip a.ql-action::after),
.quill-editor-wrap :deep(.ql-snow .ql-tooltip a.ql-remove::before) {
    color: #09DE52;
}
</style>
