<script setup>
import { reactive, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import GuestPageLayout from '@/Layouts/GuestPageLayout.vue';
import SeoHead from '@/Components/SeoHead.vue';
import Breadcrumbs from '@/Components/Storefront/Breadcrumbs.vue';
import EmptyState from '@/Components/Storefront/EmptyState.vue';
import FormField from '@/Components/Storefront/FormField.vue';
import NewsCard from '@/Components/Storefront/NewsCard.vue';
import PageHero from '@/Components/Storefront/PageHero.vue';
import Pagination from '@/Components/Storefront/Pagination.vue';
import StorefrontContainer from '@/Components/Storefront/StorefrontContainer.vue';
import UiButton from '@/Components/Storefront/UiButton.vue';

const props = defineProps({ page: { type: Object, required: true } });

const form = reactive({
    search: props.page.news.filters.search || '',
    category: props.page.news.filters.category || '',
});

watch(() => props.page.news.filters, (filters) => {
    form.search = filters.search || '';
    form.category = filters.category || '';
}, { deep: true });

const submit = () => {
    router.get(route('news.index'), {
        search: form.search || undefined,
        category: form.category || undefined,
    }, { preserveState: true, preserveScroll: false, replace: true });
};

const selectCategory = (slug) => {
    form.category = slug;
    submit();
};

const clear = () => {
    form.search = '';
    form.category = '';
    router.get(route('news.index'), {}, { preserveState: true, preserveScroll: false, replace: true });
};
</script>

<template>
    <SeoHead v-bind="page.seo" :json-ld="page.json_ld" />
    <GuestPageLayout>
        <PageHero v-bind="page.hero">
            <Breadcrumbs :items="page.breadcrumbs" class="mt-6" />
        </PageHero>

        <StorefrontContainer class="py-10">
            <form class="storefront-card grid gap-4 p-5 md:grid-cols-[minmax(0,1fr)_auto]" role="search" @submit.prevent="submit">
                <FormField id="news-search" label="Tim bai viet">
                    <template #default="{ id, describedBy }">
                        <input :id="id" v-model="form.search" :aria-describedby="describedBy" type="search" maxlength="100" class="w-full rounded-lg border border-line bg-surface-card px-3 py-2.5 text-sm">
                    </template>
                </FormField>
                <div class="flex items-end gap-2">
                    <UiButton type="submit">Tim kiem</UiButton>
                    <UiButton type="button" variant="outline" @click="clear">Xoa loc</UiButton>
                </div>
            </form>

            <div class="mt-5 flex flex-wrap gap-2" aria-label="Danh muc tin tuc">
                <button type="button" class="rounded-lg border px-4 py-2 text-sm" :class="!form.category ? 'border-brand bg-brand text-brand-contrast' : 'border-line bg-surface-card text-content-secondary'" @click="selectCategory('')">
                    Tat ca
                </button>
                <button
                    v-for="category in page.news.categories"
                    :key="category.id"
                    type="button"
                    class="rounded-lg border px-4 py-2 text-sm"
                    :class="form.category === category.slug ? 'border-brand bg-brand text-brand-contrast' : 'border-line bg-surface-card text-content-secondary'"
                    :aria-pressed="form.category === category.slug"
                    @click="selectCategory(category.slug)"
                >
                    {{ category.name }} ({{ category.posts_count }})
                </button>
            </div>

            <div v-if="page.news.items.length" class="mt-8 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                <NewsCard v-for="post in page.news.items" :key="post.id" :post="post" />
            </div>

            <EmptyState v-else class="mt-8" title="Khong tim thay bai viet phu hop." action-label="Xoa bo loc" :action-href="route('news.index')" />

            <Pagination class="mt-10" :links="page.news.pagination.links" />
        </StorefrontContainer>
    </GuestPageLayout>
</template>
