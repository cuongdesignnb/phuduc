<script setup>
import GuestPageLayout from '@/Layouts/GuestPageLayout.vue';
import SeoHead from '@/Components/SeoHead.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, watch, onMounted } from 'vue';

const props = defineProps({ posts: Object, categories: Array, filters: Object, seo: Object, jsonLd: [Object, Array] });
const search = ref(props.filters?.search || '');
const selectedCategory = ref(props.filters?.category || '');

let t;
watch([search, selectedCategory], () => {
    clearTimeout(t);
    t = setTimeout(() => {
        router.get(route('news.index'), {
            search: search.value || undefined,
            category: selectedCategory.value || undefined,
        }, { preserveState: true, replace: true });
    }, 300);
});

onMounted(() => {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) { entry.target.classList.add('revealed'); observer.unobserve(entry.target); }
        });
    }, { threshold: 0.1 });
    document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
});
</script>

<template>
    <SeoHead v-bind="seo" :json-ld="jsonLd" />
    <GuestPageLayout>
        <!-- Hero -->
        <section class="relative py-20 overflow-hidden">
            <div class="absolute inset-0 bg-grid opacity-20 pointer-events-none"></div>
            <div class="absolute top-0 left-1/4 w-[400px] h-[400px] bg-brand-primary/[0.05] rounded-full blur-[120px] pointer-events-none"></div>
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 relative">
                <p class="section-tag">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                    Tin tức & Cập nhật
                </p>
                <h1 class="section-title">Tin tức <span class="text-brand-hover">ngành xe điện</span></h1>
            </div>
        </section>

        <div class="storefront-divider"></div>

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-12">
            <!-- Filters -->
            <div class="flex flex-wrap gap-4 mb-8 items-center">
                <div class="relative w-72">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-ink-light" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input v-model="search" type="text" placeholder="Tìm bài viết..." class="w-full pl-10 pr-4 py-2.5 bg-white border border-surface-border rounded-xl text-sm text-ink-primary placeholder-ink-light focus:outline-none focus:border-brand-primary focus:ring-1 focus:ring-brand-primary/20 transition" />
                </div>
                <div class="flex gap-2 flex-wrap">
                    <button @click="selectedCategory = ''" :class="!selectedCategory ? 'bg-brand-primary text-ink-primary border-brand-primary shadow-sm' : 'bg-white text-ink-secondary border-surface-border hover:text-brand-hover hover:border-brand-primary/30'" class="px-4 py-2 rounded-xl text-xs font-medium border transition-all duration-300">Tất cả</button>
                    <button v-for="cat in categories" :key="cat.id" @click="selectedCategory = cat.slug" :class="selectedCategory === cat.slug ? 'bg-brand-primary text-ink-primary border-brand-primary shadow-sm' : 'bg-white text-ink-secondary border-surface-border hover:text-brand-hover hover:border-brand-primary/30'" class="px-4 py-2 rounded-xl text-xs font-medium border transition-all duration-300">
                        {{ cat.name }} ({{ cat.posts_count }})
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <Link v-for="(post, i) in posts.data" :key="post.id" :href="route('news.show', post.slug)" class="group storefront-card-interactive overflow-hidden reveal bg-white border border-surface-border" :style="`transition-delay: ${(i % 3) * 80}ms`">
                    <div class="aspect-video bg-surface-muted overflow-hidden border-b border-surface-border">
                        <img v-if="post.featured_image" :src="'/storage/' + post.featured_image" :alt="post.title" class="w-full h-full object-cover group-hover:scale-105 transition duration-500" />
                    </div>
                    <div class="p-5">
                        <p class="text-xs mb-2"><span class="text-brand-hover font-semibold">{{ post.category?.name || 'Chung' }}</span> <span class="text-surface-border">·</span> <span class="text-ink-light">{{ new Date(post.created_at).toLocaleDateString('vi-VN') }}</span></p>
                        <h3 class="text-sm font-semibold text-ink-primary group-hover:text-brand-hover transition-colors line-clamp-2 leading-snug">{{ post.title }}</h3>
                        <p v-if="post.summary" class="mt-2 text-xs text-ink-secondary line-clamp-3">{{ post.summary }}</p>
                    </div>
                </Link>
            </div>

            <div v-if="!posts.data.length" class="text-center py-24 text-ink-secondary">Không tìm thấy bài viết nào.</div>

            <div v-if="posts.links?.length > 3" class="mt-12 flex justify-center gap-2">
                <Link v-for="link in posts.links" :key="link.label" :href="link.url || '#'"
                    :class="[link.active ? 'bg-brand-primary text-ink-primary border-brand-primary shadow-md shadow-brand-primary/10' : 'bg-white border border-surface-border text-ink-secondary hover:text-brand-hover hover:border-brand-primary/30', !link.url ? 'opacity-30 pointer-events-none' : '']"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-300 border" v-html="link.label" preserve-state />
            </div>
        </div>
    </GuestPageLayout>
</template>
