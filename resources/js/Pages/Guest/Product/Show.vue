<script setup>
import { computed, ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import GuestPageLayout from '@/Layouts/GuestPageLayout.vue';
import ProductViewer360 from '@/Components/ProductViewer360.vue';
import SeoHead from '@/Components/SeoHead.vue';
import Breadcrumbs from '@/Components/Storefront/Breadcrumbs.vue';
import EmptyState from '@/Components/Storefront/EmptyState.vue';
import ProductCard from '@/Components/Storefront/ProductCard.vue';
import ProductGallery from '@/Components/Storefront/ProductGallery.vue';
import ProductReviewForm from '@/Components/Storefront/ProductReviewForm.vue';
import ProductReviewList from '@/Components/Storefront/ProductReviewList.vue';
import ProductReviewSummary from '@/Components/Storefront/ProductReviewSummary.vue';
import QuantityStepper from '@/Components/Storefront/QuantityStepper.vue';
import RichContent from '@/Components/Storefront/RichContent.vue';
import SectionHeader from '@/Components/Storefront/SectionHeader.vue';
import StorefrontContainer from '@/Components/Storefront/StorefrontContainer.vue';
import UiButton from '@/Components/Storefront/UiButton.vue';

const props = defineProps({ page: { type: Object, required: true } });

const inertiaPage = usePage();
const quantity = ref(1);
const processing = ref(false);
const product = computed(() => props.page.product);
const site = computed(() => inertiaPage.props.site || {});
const contactPhone = computed(() => site.value.hotline || site.value.phone || '');
const selectedVariantId = ref(null);
const activeVariant = computed(() => product.value.variants?.find((variant) => variant.id === selectedVariantId.value) || product.value.variants?.[0] || null);
const displayPrice = computed(() => activeVariant.value?.price_display || product.value.price_display);
const canPurchase = computed(() => !product.value.variants?.length && product.value.price > 0 && product.value.stock > 0);

const selectVariant = (variant) => {
    selectedVariantId.value = variant.id;
};

const addToCart = () => {
    const value = Number.parseInt(quantity.value, 10);
    quantity.value = Number.isFinite(value) && value > 0 ? value : 1;
    processing.value = true;
    router.post(route('cart.add'), { product_id: product.value.id, quantity: quantity.value }, {
        preserveScroll: true,
        onFinish: () => {
            processing.value = false;
        },
    });
};
</script>

<template>
    <SeoHead v-bind="page.seo" :json-ld="page.json_ld" />
    <GuestPageLayout>
        <section class="border-b border-line-subtle bg-gradient-to-br from-surface-muted via-surface-page to-brand-soft/35">
            <StorefrontContainer size="content" class="py-4 sm:py-5">
                <div class="flex flex-wrap items-center gap-x-3 gap-y-1.5">
                    <Breadcrumbs :items="page.breadcrumbs" variant="minimal" />
                    <span v-if="page.hero.eyebrow" class="h-4 w-px bg-line-strong" aria-hidden="true" />
                    <p v-if="page.hero.eyebrow" class="font-display text-[0.6875rem] font-bold uppercase tracking-[0.18em] text-brand-text">{{ page.hero.eyebrow }}</p>
                    <span v-if="product.sku" class="rounded-full bg-surface-muted px-2 py-1 text-[0.6875rem] font-semibold text-content-secondary">SKU {{ product.sku }}</span>
                </div>
                <h1 class="mt-2 max-w-6xl font-display text-2xl font-bold tracking-tight text-content-primary sm:text-3xl lg:text-4xl">{{ page.hero.title }}</h1>
            </StorefrontContainer>
        </section>

        <StorefrontContainer class="py-5 sm:py-7">
            <div class="grid gap-6 lg:grid-cols-[minmax(0,1.1fr)_minmax(320px,0.9fr)] lg:gap-8">
                <div class="space-y-5">
                    <ProductViewer360 v-if="product.spin_frames.length" :frames="product.spin_frames" />
                    <ProductGallery :images="product.gallery" :title="product.name" :selected-id="activeVariant?.image_id || null" />
                </div>

                <aside class="space-y-5 self-start lg:sticky lg:top-5">
                    <div class="overflow-hidden rounded-2xl border border-line bg-surface-card shadow-card">
                        <div class="bg-gradient-to-r from-brand-soft via-surface-card to-surface-card px-5 pb-5 pt-4 sm:px-6 sm:pt-5">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <p class="text-xs font-bold uppercase tracking-[0.16em] text-brand-text">Báo giá tham khảo</p>
                                <span class="inline-flex items-center gap-1.5 text-xs font-medium text-content-muted"><span class="h-1.5 w-1.5 rounded-full bg-brand" aria-hidden="true" />Tư vấn theo cấu hình</span>
                            </div>
                            <p class="mt-2 font-display text-3xl font-bold tracking-tight text-content-primary">{{ displayPrice }}</p>
                        </div>

                        <div v-if="product.variants?.length" class="border-t border-line px-5 py-5 sm:px-6">
                            <div class="flex items-center justify-between gap-4">
                                <h2 class="font-display text-lg font-bold text-content-primary">Chọn cấu hình</h2>
                                <span class="rounded-full bg-surface-muted px-2.5 py-1 text-xs font-semibold text-content-secondary">{{ product.variants.length }} lựa chọn</span>
                            </div>
                            <div class="mt-3 space-y-2.5" role="radiogroup" aria-label="Các phiên bản sản phẩm">
                                <button
                                    v-for="variant in product.variants"
                                    :key="variant.id"
                                    type="button"
                                    class="flex w-full items-start justify-between gap-4 rounded-xl border p-3.5 text-left transition"
                                    :class="activeVariant?.id === variant.id ? 'border-brand bg-brand-soft ring-2 ring-brand/25' : 'border-line bg-surface-page/60 hover:border-brand-border hover:bg-brand-soft/30'"
                                    role="radio"
                                    :aria-checked="activeVariant?.id === variant.id"
                                    @click="selectVariant(variant)"
                                >
                                    <span class="min-w-0">
                                        <strong class="block text-sm font-semibold leading-5 text-content-primary">{{ variant.name }}</strong>
                                        <span v-if="variant.note" class="mt-1 block text-xs leading-5 text-content-muted">{{ variant.note }}</span>
                                    </span>
                                    <span class="shrink-0 text-sm font-bold text-content-primary">{{ variant.price_display }}</span>
                                </button>
                            </div>
                            <p class="mt-3 text-xs leading-5 text-content-muted">Cấu hình và giá được xác nhận lại theo nhu cầu thực tế.</p>
                        </div>

                        <form v-else-if="canPurchase" class="flex flex-wrap items-center gap-4 border-t border-line px-5 py-5 sm:px-6" @submit.prevent="addToCart">
                            <QuantityStepper v-model="quantity" :min="1" :max="99" />
                            <UiButton type="submit" :disabled="processing">{{ processing ? 'Đang thêm' : 'Thêm vào giỏ hàng' }}</UiButton>
                        </form>

                        <div v-else class="border-t border-line px-5 py-5 sm:px-6">
                            <UiButton v-if="contactPhone" class="w-full" :href="`tel:${contactPhone}`">Liên hệ báo giá</UiButton>
                            <UiButton v-else class="w-full" :href="route('about')" variant="outline">Thông tin liên hệ</UiButton>
                        </div>
                    </div>

                    <div v-if="product.specifications.length" class="overflow-hidden rounded-2xl border border-line bg-surface-card shadow-card">
                        <div class="flex items-center justify-between gap-4 border-b border-line bg-gradient-to-r from-brand-soft via-surface-card to-surface-card px-5 py-4">
                            <div class="flex items-center gap-3">
                                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-surface-inverse text-xs font-bold tracking-wide text-content-inverse shadow-ds-sm">TS</span>
                                <div>
                                    <h2 class="font-display text-xl font-bold text-content-primary">Thông số kỹ thuật</h2>
                                    <p class="mt-0.5 text-xs text-content-muted">Cấu hình tham khảo</p>
                                </div>
                            </div>
                            <span class="rounded-full border border-brand-border bg-surface-card px-2.5 py-1 text-xs font-semibold text-brand-text">{{ product.specifications.length }} chỉ tiêu</span>
                        </div>
                        <dl class="space-y-2 p-3">
                            <div v-for="specification in product.specifications" :key="`${specification.key}-${specification.value}`" class="group grid grid-cols-[minmax(0,0.8fr)_minmax(0,1.2fr)] items-start gap-3 rounded-xl border border-transparent bg-surface-page/70 px-3.5 py-3 text-sm transition hover:border-brand-border hover:bg-brand-soft/40">
                                <dt class="flex items-start gap-2 leading-5 text-content-secondary">
                                    <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-brand" aria-hidden="true" />
                                    <span>{{ specification.label }}</span>
                                </dt>
                                <dd class="text-right font-semibold leading-5 text-content-primary">{{ specification.value }}</dd>
                            </div>
                        </dl>
                    </div>
                </aside>
            </div>

            <section class="mt-12">
                <SectionHeader title="Mô tả sản phẩm" />
                <div class="storefront-card mt-5 p-6">
                    <RichContent v-if="product.description_html" :html="product.description_html" />
                    <EmptyState v-else title="Chưa có mô tả" />
                </div>
            </section>

            <section class="mt-12 space-y-5">
                <ProductReviewSummary :summary="product.review_summary" />
                <ProductReviewForm :product-id="product.id" />
                <ProductReviewList :reviews="product.reviews" />
            </section>

            <section v-if="page.related_products.length" class="mt-14 overflow-hidden rounded-3xl border border-line bg-gradient-to-br from-surface-card via-surface-page to-brand-soft/40 px-5 py-8 sm:px-8">
                <div class="flex flex-col gap-5 border-b border-line pb-6 sm:flex-row sm:items-end sm:justify-between">
                    <div class="max-w-2xl">
                        <p class="mb-3 font-display text-xs font-bold uppercase tracking-[0.2em] text-brand-text">Khám phá thêm</p>
                        <h2 class="font-display text-3xl font-bold tracking-tight text-content-primary">Sản phẩm gợi ý</h2>
                        <p class="mt-3 text-base leading-relaxed text-content-secondary">Các thiết bị cùng nhóm, phù hợp để so sánh cấu hình và lựa chọn giải pháp.</p>
                    </div>
                    <Link
                        :href="route('products.index')"
                        class="group inline-flex min-h-11 shrink-0 items-center gap-3 self-start rounded-full border border-line-strong bg-surface-card px-5 text-sm font-semibold text-content-primary shadow-ds-sm transition hover:-translate-y-0.5 hover:border-brand-border hover:bg-brand-soft sm:self-auto"
                    >
                        Xem tất cả sản phẩm
                        <span aria-hidden="true" class="grid h-6 w-6 place-items-center rounded-full bg-brand text-base leading-none text-brand-contrast transition group-hover:translate-x-0.5">→</span>
                    </Link>
                </div>
                <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    <ProductCard v-for="related in page.related_products" :key="related.id" :product="related" variant="featured" />
                </div>
            </section>
        </StorefrontContainer>
    </GuestPageLayout>
</template>
