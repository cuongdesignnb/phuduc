<script setup>
import { computed, ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import GuestPageLayout from '@/Layouts/GuestPageLayout.vue';
import ProductViewer360 from '@/Components/ProductViewer360.vue';
import SeoHead from '@/Components/SeoHead.vue';
import Breadcrumbs from '@/Components/Storefront/Breadcrumbs.vue';
import EmptyState from '@/Components/Storefront/EmptyState.vue';
import PageHero from '@/Components/Storefront/PageHero.vue';
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
        <PageHero v-bind="page.hero">
            <Breadcrumbs :items="page.breadcrumbs" class="mt-6" />
        </PageHero>

        <StorefrontContainer class="py-10">
            <div class="grid gap-8 lg:grid-cols-[minmax(0,1.1fr)_minmax(320px,0.9fr)]">
                <div class="space-y-5">
                    <ProductViewer360 v-if="product.spin_frames.length" :frames="product.spin_frames" />
                    <ProductGallery :images="product.gallery" :title="product.name" />
                </div>

                <aside class="space-y-6">
                    <div class="storefront-card p-6">
                        <p v-if="product.sku" class="text-xs font-semibold uppercase tracking-wide text-content-muted">SKU: {{ product.sku }}</p>
                        <h1 class="mt-2 font-display text-3xl font-bold text-content-primary">{{ product.name }}</h1>
                        <p class="mt-4 font-display text-3xl font-bold text-content-primary">{{ product.price_display }}</p>

                        <form v-if="product.price > 0" class="mt-6 flex flex-wrap items-center gap-4" @submit.prevent="addToCart">
                            <QuantityStepper v-model="quantity" :min="1" :max="99" />
                            <UiButton type="submit" :disabled="processing">{{ processing ? 'Đang thêm' : 'Thêm vào giỏ hàng' }}</UiButton>
                        </form>

                        <div v-else class="mt-6">
                            <UiButton v-if="contactPhone" :href="`tel:${contactPhone}`">Liên hệ báo giá</UiButton>
                            <UiButton v-else :href="route('about')" variant="outline">Thông tin liên hệ</UiButton>
                        </div>
                    </div>

                    <div v-if="product.specifications.length" class="storefront-card p-6">
                        <h2 class="font-display text-xl font-bold text-content-primary">Thông số kỹ thuật</h2>
                        <dl class="mt-4 divide-y divide-line">
                            <div v-for="specification in product.specifications" :key="`${specification.key}-${specification.value}`" class="grid grid-cols-2 gap-4 py-3 text-sm">
                                <dt class="text-content-muted">{{ specification.label }}</dt>
                                <dd class="font-medium text-content-primary">{{ specification.value }}</dd>
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

            <section v-if="page.related_products.length" class="mt-12">
                <SectionHeader title="Sản phẩm gợi ý" />
                <div class="mt-5 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <ProductCard v-for="related in page.related_products" :key="related.id" :product="related" />
                </div>
            </section>
        </StorefrontContainer>
    </GuestPageLayout>
</template>
