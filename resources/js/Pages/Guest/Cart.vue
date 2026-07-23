<script setup>
import { nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import GuestPageLayout from '@/Layouts/GuestPageLayout.vue';
import SeoHead from '@/Components/SeoHead.vue';
import Breadcrumbs from '@/Components/Storefront/Breadcrumbs.vue';

const props = defineProps({ page: { type: Object, required: true } });
const confirmOpen = ref(false);
const confirmDialog = ref(null);
const clearButton = ref(null);
const clearProcessing = ref(false);

const handleKeydown = (event) => {
    if (event.key === 'Escape' && confirmOpen.value) cancelClear();
};

const openClearConfirmation = () => {
    confirmOpen.value = true;
};

const cancelClear = () => {
    confirmOpen.value = false;
    nextTick(() => clearButton.value?.focus());
};

const confirmClear = () => {
    clearProcessing.value = true;
    router.post(route('cart.clear'), {}, {
        preserveScroll: true,
        onFinish: () => {
            clearProcessing.value = false;
            confirmOpen.value = false;
        },
    });
};

watch(confirmOpen, async (open) => {
    if (open) {
        await nextTick();
        confirmDialog.value?.focus();
    }
});

onMounted(() => window.addEventListener('keydown', handleKeydown));
onBeforeUnmount(() => window.removeEventListener('keydown', handleKeydown));

const updateQty = (productId, quantity) => router.patch(route('cart.update'), { product_id: productId, quantity }, { preserveScroll: true });
const removeItem = (productId) => router.delete(route('cart.remove'), { data: { product_id: productId }, preserveScroll: true });
</script>

<template>
    <SeoHead v-bind="page.seo" />
    <GuestPageLayout>
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <Breadcrumbs :items="page.breadcrumbs" class="mb-6" />
            <h1 class="mb-8 text-2xl font-display font-bold text-content-primary">Giỏ hàng</h1>

            <div v-if="page.cart.warnings.length" class="mb-6 space-y-2" role="status" aria-live="polite">
                <p v-for="warning in page.cart.warnings" :key="warning" class="rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-800">{{ warning }}</p>
            </div>

            <div v-if="page.cart.items.length" class="space-y-6">
                <div class="overflow-hidden rounded-lg border border-line bg-surface-card">
                    <div v-for="item in page.cart.items" :key="item.product_id" class="flex flex-wrap items-center gap-4 border-b border-line p-4 last:border-0 sm:flex-nowrap">
                        <img v-if="item.image_url" :src="item.image_url" :alt="item.name" class="h-16 w-16 rounded-lg border border-line object-cover" />
                        <div v-else class="h-16 w-16 rounded-lg border border-line bg-surface-muted" aria-hidden="true"></div>
                        <div class="min-w-0 flex-1">
                            <Link :href="route('products.show', item.slug)" class="text-sm font-semibold text-content-primary hover:text-brand-text">{{ item.name }}</Link>
                            <p class="mt-1 text-sm font-semibold text-brand-text">{{ item.unit_price_display }}</p>
                        </div>
                        <div class="flex items-center rounded-lg border border-line bg-surface-muted" :aria-label="`Số lượng ${item.name}`">
                            <button type="button" class="grid min-h-11 min-w-11 place-items-center" :disabled="item.quantity <= 1" :aria-label="`Giảm số lượng ${item.name}`" @click="updateQty(item.product_id, item.quantity - 1)">−</button>
                            <output class="min-w-10 text-center text-sm font-semibold" :aria-label="`Số lượng ${item.name}: ${item.quantity}`">{{ item.quantity }}</output>
                            <button type="button" class="grid min-h-11 min-w-11 place-items-center" :disabled="item.quantity >= item.max_quantity" :aria-label="`Tăng số lượng ${item.name}`" @click="updateQty(item.product_id, item.quantity + 1)">+</button>
                        </div>
                        <div class="w-32 text-right text-sm font-bold text-content-primary">{{ item.subtotal_display }}</div>
                        <button type="button" class="grid min-h-11 min-w-11 place-items-center text-content-muted hover:text-danger" :aria-label="`Xóa ${item.name}`" @click="removeItem(item.product_id)">×</button>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-4 rounded-lg border border-line bg-surface-card p-6">
                    <div>
                        <span class="text-lg font-semibold text-content-secondary">Tổng cộng</span>
                        <span class="ml-3 text-sm text-content-muted">{{ page.cart.summary.quantity_count }} sản phẩm</span>
                    </div>
                    <span class="text-2xl font-display font-bold text-brand-text">{{ page.cart.summary.total_display }}</span>
                </div>

                <div class="flex flex-wrap justify-end gap-4">
                    <button ref="clearButton" type="button" class="btn-outline" aria-haspopup="dialog" @click="openClearConfirmation">Xóa giỏ hàng</button>
                    <Link :href="route('products.index')" class="btn-outline">Tiếp tục mua</Link>
                    <Link v-if="page.cart.can_checkout" :href="route('checkout.index')" class="btn-primary">Tiến hành thanh toán</Link>
                </div>
            </div>

            <div v-else class="py-24 text-center">
                <p class="mb-4 text-content-secondary">Giỏ hàng đang trống.</p>
                <Link :href="route('products.index')" class="text-brand-text hover:text-brand-hover">Xem sản phẩm</Link>
            </div>
        </div>

        <div v-if="confirmOpen" class="fixed inset-0 z-50 grid place-items-center bg-black/40 p-4" role="presentation">
            <section ref="confirmDialog" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="clear-cart-title" class="w-full max-w-md rounded-lg border border-line bg-surface-card p-6 shadow-xl" @keydown.esc="cancelClear">
                <h2 id="clear-cart-title" class="text-lg font-display font-bold text-content-primary">Xóa toàn bộ giỏ hàng?</h2>
                <p class="mt-2 text-sm text-content-secondary">Các sản phẩm hiện tại sẽ bị xóa khỏi giỏ hàng.</p>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" class="btn-outline" :disabled="clearProcessing" @click="cancelClear">Hủy</button>
                    <button type="button" class="btn-primary" :disabled="clearProcessing" @click="confirmClear">{{ clearProcessing ? 'Đang xử lý...' : 'Xác nhận xóa' }}</button>
                </div>
            </section>
        </div>
    </GuestPageLayout>
</template>
