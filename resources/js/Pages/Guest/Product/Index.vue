<script setup>
import GuestPageLayout from '@/Layouts/GuestPageLayout.vue';
import SeoHead from '@/Components/SeoHead.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, watch, onMounted } from 'vue';

const props = defineProps({ products: Object, filters: Object, seo: Object, jsonLd: [Object, Array] });

const search = ref(props.filters?.search || '');
let t;
watch(search, () => { clearTimeout(t); t = setTimeout(() => { router.get(route('products.index'), { search: search.value || undefined }, { preserveState: true, replace: true }); }, 400); });

const formatPrice = (price) => {
    if (!price || price == 0) return 'Liên hệ';
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price);
};

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
        <!-- Hero Banner -->
        <section class="relative py-20 overflow-hidden">
            <div class="absolute inset-0 bg-grid opacity-20 pointer-events-none"></div>
            <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-brand-primary/[0.05] rounded-full blur-[120px] pointer-events-none"></div>
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 relative">
                <p class="section-tag">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    Danh mục sản phẩm
                </p>
                <h1 class="section-title">Xe điện <span class="text-brand-hover">công nghiệp</span></h1>
                <p class="section-subtitle">Khám phá dòng sản phẩm xe điện đa dạng cho mọi nhu cầu vận chuyển</p>
            </div>
        </section>

        <div class="storefront-divider"></div>

        <!-- Products Grid -->
        <section class="py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Search -->
                <div class="flex justify-end mb-8">
                    <div class="relative w-80">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-ink-light" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input v-model="search" type="text" placeholder="Tìm kiếm sản phẩm..."
                            class="w-full pl-10 pr-4 py-3 bg-white border border-surface-border rounded-xl text-sm text-ink-primary placeholder-ink-light focus:outline-none focus:border-brand-primary focus:ring-1 focus:ring-brand-primary/20 transition-all duration-300" />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <Link v-for="(product, i) in products.data" :key="product.id"
                        :href="route('products.show', product.slug)"
                        class="group product-card reveal"
                        :style="`transition-delay: ${(i % 4) * 80}ms`">
                        <div class="product-img-wrap">
                            <img v-if="product.images?.length" :src="'/storage/' + product.images[0].image_path" :alt="product.name" />
                            <div v-else class="w-full h-full flex items-center justify-center text-ink-light bg-surface-muted border-b border-surface-border">
                                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="0.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <div class="absolute top-3 right-3 opacity-0 group-hover:opacity-100 transition-all duration-300 translate-y-2 group-hover:translate-y-0">
                                <span class="inline-flex items-center gap-1 bg-brand-primary text-ink-primary px-2.5 py-1 rounded-lg text-[10px] font-display font-bold uppercase tracking-wider">
                                    Chi tiết
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </span>
                            </div>
                        </div>
                        <div class="p-5">
                            <h3 class="text-sm font-semibold text-ink-primary group-hover:text-brand-hover transition-colors duration-300 line-clamp-2 leading-snug">{{ product.name }}</h3>
                            <div class="mt-3">
                                <span class="font-display font-bold text-lg" :class="product.price > 0 ? 'text-brand-hover' : 'text-ink-secondary'">
                                    {{ formatPrice(product.price) }}
                                </span>
                            </div>
                        </div>
                        <div class="h-0.5 bg-gradient-to-r from-brand-primary to-orange-500 scale-x-0 group-hover:scale-x-100 transition-transform duration-500 origin-left"></div>
                    </Link>
                </div>

                <div v-if="!products.data.length" class="text-center py-24">
                    <svg class="w-16 h-16 mx-auto text-ink-light mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="0.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <p class="text-ink-secondary text-lg">Không tìm thấy sản phẩm nào.</p>
                </div>

                <!-- Pagination -->
                <div v-if="products.links?.length > 3" class="mt-12 flex justify-center gap-2">
                    <Link v-for="link in products.links" :key="link.label" :href="link.url || '#'"
                        :class="[
                            'px-4 py-2 rounded-lg text-sm font-medium transition-all duration-300 border',
                            link.active
                                ? 'bg-brand-primary text-ink-primary border-brand-primary shadow-md shadow-brand-primary/10'
                                : 'bg-white border-surface-border text-ink-secondary hover:text-brand-hover hover:border-brand-primary/30',
                            !link.url ? 'opacity-30 pointer-events-none' : ''
                        ]"
                        v-html="link.label" preserve-state />
                </div>
            </div>
        </section>
    </GuestPageLayout>
</template>
