<script setup>
import { ref } from 'vue';
import { QuillEditor } from '@vueup/vue-quill';
import '@vueup/vue-quill/dist/vue-quill.snow.css';
import AdminMediaPicker from '@/Components/Admin/AdminMediaPicker.vue';

const props = defineProps({ modelValue: { type: String, default: '' }, height: { type: Number, default: 300 } });
const emit = defineEmits(['update:modelValue']);
const showPicker = ref(false); const quillRef = ref(null);
const toolbarOptions = [[{ header: [1, 2, 3, 4, false] }], ['bold', 'italic', 'underline', 'strike'], [{ color: [] }, { background: [] }], [{ align: [] }], [{ list: 'ordered' }, { list: 'bullet' }], ['blockquote', 'code-block'], ['link', 'image', 'video'], ['clean']];
const editorOptions = { theme: 'snow', modules: { toolbar: { container: toolbarOptions, handlers: { image: () => { showPicker.value = true; } } } }, placeholder: 'Nhập nội dung...' };
const selectMedia = (media) => { showPicker.value = false; const quill = quillRef.value?.getQuill(); if (!quill || !media.url) return; const range = quill.getSelection(true); quill.insertEmbed(range.index, 'image', media.url); quill.setSelection(range.index + 1); };
</script>

<template>
    <div class="quill-editor-wrap relative"><QuillEditor ref="quillRef" :content="modelValue" content-type="html" :options="editorOptions" :style="{ height: `${height}px` }" @update:content="emit('update:modelValue', $event)" /><AdminMediaPicker :open="showPicker" media-type="image" @close="showPicker = false" @select="selectMedia" /></div>
</template>
