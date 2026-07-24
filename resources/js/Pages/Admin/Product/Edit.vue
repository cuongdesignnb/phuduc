<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AdminConfirmDialog from '@/Components/Admin/AdminConfirmDialog.vue';
import AdminDataCard from '@/Components/Admin/AdminDataCard.vue';
import AdminErrorSummary from '@/Components/Admin/AdminErrorSummary.vue';
import AdminFormField from '@/Components/Admin/AdminFormField.vue';
import AdminMediaPicker from '@/Components/Admin/AdminMediaPicker.vue';
import AdminPageHeader from '@/Components/Admin/AdminPageHeader.vue';
import AdminSelect from '@/Components/Admin/AdminSelect.vue';
import AdminTextInput from '@/Components/Admin/AdminTextInput.vue';
import AdminTextarea from '@/Components/Admin/AdminTextarea.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({ page: { type: Object, required: true } });
const module = props.page.module;
const product = module.product;
const form = useForm({ name: product?.name || '', slug: product?.slug || '', sku: product?.sku || '', description: product?.description || '', price: product?.price ?? 0, stock: product?.stock ?? 0, status: product?.status || 'active', specifications: product?.specifications?.length ? product.specifications : [{ key: '', value: '' }], version: product?.version || null });
const showPicker = ref(false);
const deletingImage = ref(null);
const uploading = ref(false);
const addSpec = () => form.specifications.push({ key: '', value: '' });
const removeSpec = (index) => form.specifications.splice(index, 1);
const save = () => { product ? form.put(route('admin.products.update', product.id)) : form.post(route('admin.products.store')); };
const uploadImages = (event, is360) => { if (!product || !event.target.files.length) return; const data = new FormData(); Array.from(event.target.files).forEach((file) => data.append('images[]', file)); if (is360) data.append('is_360', '1'); uploading.value = true; router.post(route('admin.products.images.upload', product.id), data, { forceFormData: true, preserveScroll: true, onFinish: () => { uploading.value = false; } }); };
const selectMedia = (media) => { showPicker.value = false; if (product) router.post(route('admin.products.images.from-media', product.id), { media_id: media.id, is_360: 0 }, { preserveScroll: true }); };
const deleteImage = () => { router.delete(route('admin.products.images.delete', [product.id, deletingImage.value.id]), { preserveScroll: true, onFinish: () => { deletingImage.value = null; } }); };
</script>

<template>
    <Head :title="page.meta.title" />
    <AuthenticatedLayout>
        <AdminPageHeader :title="page.meta.title"><Link :href="route('admin.products.index')" class="text-sm text-admin-content-muted hover:text-admin-content">Quay lại</Link></AdminPageHeader>
        <div class="mt-6 space-y-6"><AdminErrorSummary :errors="form.errors" /><AdminDataCard title="Thông tin sản phẩm"><form class="space-y-5" @submit.prevent="save"><div class="grid gap-4 md:grid-cols-2"><AdminFormField label="Tên sản phẩm" for-id="product-name" :error="form.errors.name"><AdminTextInput id="product-name" v-model="form.name" /></AdminFormField><AdminFormField label="Slug" for-id="product-slug" :error="form.errors.slug" hint="Để trống để tự sinh slug duy nhất"><AdminTextInput id="product-slug" v-model="form.slug" /></AdminFormField><AdminFormField label="SKU" for-id="product-sku" :error="form.errors.sku"><AdminTextInput id="product-sku" v-model="form.sku" /></AdminFormField><AdminFormField label="Giá VND" for-id="product-price" :error="form.errors.price" hint="Giá 0 hiển thị là Liên hệ"><AdminTextInput id="product-price" v-model.number="form.price" type="number" /></AdminFormField><AdminFormField label="Tồn kho" for-id="product-stock" :error="form.errors.stock"><AdminTextInput id="product-stock" v-model.number="form.stock" type="number" /></AdminFormField><AdminFormField label="Trạng thái" for-id="product-status" :error="form.errors.status"><AdminSelect id="product-status" v-model="form.status" :options="module.statuses" /></AdminFormField></div><AdminFormField label="Mô tả" for-id="product-description" :error="form.errors.description"><AdminTextarea id="product-description" v-model="form.description" rows="8" /></AdminFormField><AdminFormField label="Thông số kỹ thuật" :error="form.errors.specifications"><div class="space-y-2"><div v-for="(spec, index) in form.specifications" :key="index" class="flex gap-2"><AdminTextInput v-model="spec.key" placeholder="Tên thông số" /><AdminTextInput v-model="spec.value" placeholder="Giá trị" /><button type="button" class="px-2 text-admin-danger" :aria-label="`Xóa thông số ${index + 1}`" @click="removeSpec(index)">×</button></div><button type="button" class="text-sm text-admin-accent" @click="addSpec">+ Thêm thông số</button></div></AdminFormField><div class="flex justify-end"><button type="submit" :disabled="form.processing" class="rounded-lg bg-admin-accent px-5 py-2 text-sm font-semibold text-admin-page disabled:opacity-50">{{ form.processing ? 'Đang lưu...' : 'Lưu sản phẩm' }}</button></div></form></AdminDataCard>
        <AdminDataCard v-if="product" title="Hình ảnh sản phẩm"><div class="flex flex-wrap gap-3"><label class="cursor-pointer rounded-lg border border-admin-border px-4 py-2 text-sm text-admin-content hover:bg-admin-surface-muted"><input type="file" class="sr-only" multiple accept="image/jpeg,image/png,image/webp,image/gif" :disabled="uploading" @change="uploadImages($event, false)" />Tải ảnh lên</label><button type="button" class="rounded-lg border border-admin-border px-4 py-2 text-sm text-admin-content hover:bg-admin-surface-muted" @click="showPicker = true">Chọn từ Media</button></div><div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-5"><div v-for="image in product.images" :key="image.id" class="relative border border-admin-border p-2"><img :src="image.url" :alt="image.file_name" class="aspect-square w-full object-cover" /><span v-if="image.is_360" class="mt-1 block text-xs text-admin-content-muted">Ảnh 360</span><button type="button" class="absolute right-1 top-1 rounded bg-admin-danger px-2 py-1 text-xs text-white" @click="deletingImage = image">Xóa</button></div></div></AdminDataCard></div>
        <AdminMediaPicker :open="showPicker" :items="module.media" @close="showPicker = false" @select="selectMedia" />
        <AdminConfirmDialog :open="!!deletingImage" title="Xóa ảnh sản phẩm" message="Chỉ tệp do sản phẩm sở hữu mới bị xóa. Media gốc vẫn được giữ lại." confirm-label="Xóa ảnh" danger @cancel="deletingImage = null" @confirm="deleteImage" />
    </AuthenticatedLayout>
</template>
