<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AdvancedTextEditor from '@/Components/AdvancedTextEditor.vue';
import MediaBox from '@/Components/MediaBox.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({ product: Object });

const fixText = (value) => {
    if (typeof value !== 'string') return value;
    const codes = Array.from(value).map((char) => char.charCodeAt(0));
    const isBroken = codes.some((code) => [0xc2, 0xc3, 0xc4, 0xc6, 0xca, 0xfffd].includes(code))
        || codes.some((code) => code >= 0x80 && code <= 0x9f)
        || codes.some((code, index) => code === 0xe1 && [0xba, 0xbb].includes(codes[index + 1]));
    if (!isBroken) return value;
    try {
        const bytes = Uint8Array.from(Array.from(value), (char) => char.charCodeAt(0) & 255);
        return new TextDecoder('utf-8', { fatal: false }).decode(bytes);
    } catch {
        return value;
    }
};

const form = useForm({
    name: props.product?.name ? fixText(props.product.name) : '',
    slug: props.product?.slug || '',
    description: props.product?.description ? fixText(props.product.description) : '',
    price: props.product?.price || 0,
    sku: props.product?.sku || '',
    stock: props.product?.stock || 0,
    specifications: (props.product?.specifications || []).map(s => ({ key: fixText(s.key), value: fixText(s.value) })),
    status: props.product?.status || 'active',
});

const images = ref(props.product?.images || []);
const uploadingImages = ref(false);
const uploadingImages360 = ref(false);
const showMediaBox = ref(false);
const mediaBoxFor = ref('normal');

const specs = ref(form.specifications?.length ? form.specifications : [{ key: '', value: '' }]);

const addSpec = () => specs.value.push({ key: '', value: '' });
const removeSpec = (i) => specs.value.splice(i, 1);

const save = () => {
    form.specifications = specs.value.filter(s => s.key);
    if (props.product) {
        form.put(route('admin.products.update', props.product.id));
    } else {
        form.post(route('admin.products.store'));
    }
};

const uploadImages = async (e, is360 = false) => {
    if (!props.product) return;
    const formData = new FormData();
    for (let f of e.target.files) formData.append('images[]', f);
    if (is360) formData.append('is_360', '1');
    if (is360) uploadingImages360.value = true; else uploadingImages.value = true;
    router.post(route('admin.products.images.upload', props.product.id), formData, {
        forceFormData: true,
        preserveScroll: true,
        onFinish: () => { uploadingImages.value = false; uploadingImages360.value = false; },
    });
};

const deleteImage = (imageId) => {
    if (!confirm('Xóa ảnh này?')) return;
    router.delete(route('admin.products.images.delete', [props.product.id, imageId]), { preserveScroll: true });
};

const normalImages = computed(() => (images.value || []).filter(i => !i.is_360));
const images360 = computed(() => (images.value || []).filter(i => i.is_360));

const openMediaBox = (type) => {
    mediaBoxFor.value = type;
    showMediaBox.value = true;
};

const handleMediaSelect = (media) => {
    showMediaBox.value = false;
    if (!props.product) return;
    router.post(route('admin.products.images.from-media', props.product.id), {
        media_id: media.id,
        is_360: mediaBoxFor.value === '360' ? 1 : 0,
    }, { preserveScroll: true });
};
</script>

<template>
    <Head :title="product ? 'Sửa: ' + $fixText(product.name) : 'Thêm sản phẩm mới'" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-display font-bold text-white">
                {{ product ? 'Sửa sản phẩm: ' + $fixText(product.name) : 'Thêm sản phẩm mới' }}
            </h2>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-6 space-y-6">
                <!-- Basic Info -->
                <form @submit.prevent="save" class="rounded-2xl border border-white/5 bg-carbon-900/50 backdrop-blur-sm p-6 space-y-6">
                    <h3 class="text-lg font-display font-semibold text-white">Thông tin sản phẩm</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-carbon-300 mb-1.5">Tên sản phẩm *</label>
                            <input v-model="form.name" type="text" required class="w-full rounded-xl border border-white/10 bg-carbon-800 text-white placeholder-carbon-500 py-2.5 px-4 text-sm focus:border-volt-500/50 focus:ring-1 focus:ring-volt-500/20 transition" />
                            <p v-if="form.errors.name" class="mt-1 text-sm text-red-400">{{ form.errors.name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-carbon-300 mb-1.5">Slug</label>
                            <input v-model="form.slug" type="text" placeholder="Tự sinh nếu bỏ trống" class="w-full rounded-xl border border-white/10 bg-carbon-800 text-white placeholder-carbon-500 py-2.5 px-4 text-sm focus:border-volt-500/50 focus:ring-1 focus:ring-volt-500/20 transition" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-carbon-300 mb-1.5">Giá (VNĐ)</label>
                            <input v-model.number="form.price" type="number" step="1000" min="0" class="w-full rounded-xl border border-white/10 bg-carbon-800 text-white py-2.5 px-4 text-sm focus:border-volt-500/50 focus:ring-1 focus:ring-volt-500/20 transition" />
                            <p class="text-xs text-carbon-600 mt-1">Để 0 hoặc bỏ trống = hiển thị "Liên hệ"</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-carbon-300 mb-1.5">SKU</label>
                            <input v-model="form.sku" type="text" class="w-full rounded-xl border border-white/10 bg-carbon-800 text-white py-2.5 px-4 text-sm focus:border-volt-500/50 focus:ring-1 focus:ring-volt-500/20 transition" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-carbon-300 mb-1.5">Số lượng trong kho</label>
                            <input v-model.number="form.stock" type="number" min="0" class="w-full rounded-xl border border-white/10 bg-carbon-800 text-white py-2.5 px-4 text-sm focus:border-volt-500/50 focus:ring-1 focus:ring-volt-500/20 transition" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-carbon-300 mb-1.5">Trạng thái</label>
                            <select v-model="form.status" class="w-full rounded-xl border border-white/10 bg-carbon-800 text-white py-2.5 px-4 text-sm focus:border-volt-500/50 focus:ring-1 focus:ring-volt-500/20 transition">
                                <option value="active">Đang bán</option>
                                <option value="inactive">Ngừng bán</option>
                            </select>
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-sm font-medium text-carbon-300 mb-1.5">Mô tả sản phẩm</label>
                        <AdvancedTextEditor v-model="form.description" />
                    </div>

                    <!-- Specifications -->
                    <div>
                        <div class="flex justify-between items-center mb-3">
                            <label class="block text-sm font-medium text-carbon-300">Thông số kỹ thuật</label>
                            <button type="button" @click="addSpec" class="text-sm text-volt-400 hover:text-volt-300 transition-colors">+ Thêm thông số</button>
                        </div>
                        <div v-for="(spec, i) in specs" :key="i" class="flex gap-2 mb-2">
                            <input v-model="spec.key" type="text" placeholder="Tên thông số" class="flex-1 text-sm rounded-xl border border-white/10 bg-carbon-800 text-white placeholder-carbon-500 py-2 px-3 focus:border-volt-500/50 focus:ring-1 focus:ring-volt-500/20" />
                            <input v-model="spec.value" type="text" placeholder="Giá trị" class="flex-1 text-sm rounded-xl border border-white/10 bg-carbon-800 text-white placeholder-carbon-500 py-2 px-3 focus:border-volt-500/50 focus:ring-1 focus:ring-volt-500/20" />
                            <button type="button" @click="removeSpec(i)" class="text-red-400 hover:text-red-300 px-2 transition-colors">✕</button>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" :disabled="form.processing" class="inline-flex items-center gap-2 rounded-xl bg-volt-500 px-6 py-2.5 text-sm font-semibold text-white hover:bg-volt-600 disabled:opacity-50 transition-all shadow-lg shadow-volt-500/20">
                            {{ product ? 'Cập nhật' : 'Tạo sản phẩm' }}
                        </button>
                    </div>
                </form>

                <!-- Images (only after product is saved) -->
                <div v-if="product" class="rounded-2xl border border-white/5 bg-carbon-900/50 backdrop-blur-sm p-6 space-y-6">
                    <h3 class="text-lg font-display font-semibold text-white">Hình ảnh sản phẩm</h3>
                    <div>
                        <label class="block text-sm font-medium text-carbon-300 mb-3">Ảnh thường</label>
                        <div class="flex gap-3">
                            <input type="file" multiple accept="image/*" @change="e => uploadImages(e, false)" class="text-sm text-carbon-400 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-medium file:bg-carbon-800 file:text-carbon-300 hover:file:bg-carbon-700 file:cursor-pointer file:transition" :disabled="uploadingImages" />
                            <button type="button" @click="openMediaBox('normal')" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-white/10 bg-carbon-800 text-carbon-300 text-sm hover:bg-carbon-700 hover:text-white transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                Media Library
                            </button>
                        </div>
                        <div class="flex flex-wrap gap-3 mt-4">
                            <div v-for="img in normalImages" :key="img.id" class="relative group">
                                <img :src="'/storage/' + img.image_path" class="w-24 h-24 object-cover rounded-xl border border-white/10" />
                                <button @click="deleteImage(img.id)" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 text-xs flex items-center justify-center opacity-0 group-hover:opacity-100 transition shadow-lg">✕</button>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-carbon-300 mb-3">Ảnh 360° (sẽ dùng cho Product Viewer)</label>
                        <div class="flex gap-3">
                            <input type="file" multiple accept="image/*" @change="e => uploadImages(e, true)" class="text-sm text-carbon-400 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-medium file:bg-carbon-800 file:text-carbon-300 hover:file:bg-carbon-700 file:cursor-pointer file:transition" :disabled="uploadingImages360" />
                            <button type="button" @click="openMediaBox('360')" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-white/10 bg-carbon-800 text-carbon-300 text-sm hover:bg-carbon-700 hover:text-white transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                Media Library
                            </button>
                        </div>
                        <div class="flex flex-wrap gap-3 mt-4">
                            <div v-for="img in images360" :key="img.id" class="relative group">
                                <img :src="'/storage/' + img.image_path" class="w-24 h-24 object-cover rounded-xl border border-industrial-500/30" />
                                <span class="absolute bottom-0 left-0 bg-industrial-500 text-white text-[10px] px-1.5 py-0.5 rounded-tr-lg rounded-bl-xl font-medium">360°</span>
                                <button @click="deleteImage(img.id)" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 text-xs flex items-center justify-center opacity-0 group-hover:opacity-100 transition shadow-lg">✕</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MediaBox Modal -->
        <Teleport to="body">
            <div v-if="showMediaBox" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/60 backdrop-blur-sm" @click.self="showMediaBox = false">
                <div class="bg-carbon-900 border border-white/10 p-6 rounded-2xl w-[80vw] max-w-5xl h-[75vh] overflow-y-auto shadow-2xl">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-display font-semibold text-white">Chọn từ Media Library</h3>
                        <button @click="showMediaBox = false" class="rounded-lg p-1.5 text-carbon-400 hover:bg-red-500/10 hover:text-red-400 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <MediaBox @select="handleMediaSelect" />
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>
