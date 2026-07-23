<script setup>
import GuestPageLayout from '@/Layouts/GuestPageLayout.vue';
import SeoHead from '@/Components/SeoHead.vue';
import Breadcrumbs from '@/Components/Storefront/Breadcrumbs.vue';
import EmptyState from '@/Components/Storefront/EmptyState.vue';
import PageHero from '@/Components/Storefront/PageHero.vue';
import Pagination from '@/Components/Storefront/Pagination.vue';
import ProductCard from '@/Components/Storefront/ProductCard.vue';
import ProductCatalogFilters from '@/Components/Storefront/ProductCatalogFilters.vue';
import StorefrontContainer from '@/Components/Storefront/StorefrontContainer.vue';

defineProps({ page: { type: Object, required: true } });
</script>

<template>
    <SeoHead v-bind="page.seo" :json-ld="page.json_ld" />
    <GuestPageLayout>
        <PageHero v-bind="page.hero">
            <Breadcrumbs :items="page.breadcrumbs" class="mt-6" />
        </PageHero>

        <StorefrontContainer class="py-10">
            <ProductCatalogFilters :filters="page.catalog.filters" :sort-options="page.catalog.sort_options" />

            <div v-if="page.catalog.items.length" class="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <ProductCard v-for="product in page.catalog.items" :key="product.id" :product="product" />
            </div>

            <EmptyState
                v-else
                class="mt-8"
                :title="page.catalog.filters.search || page.catalog.filters.min_price || page.catalog.filters.max_price ? 'Khong tim thay san pham phu hop.' : 'Chua co san pham duoc cong bo.'"
                action-label="Xoa bo loc"
                :action-href="route('products.index')"
            />

            <Pagination class="mt-10" :links="page.catalog.pagination.links" />
        </StorefrontContainer>
    </GuestPageLayout>
</template>
