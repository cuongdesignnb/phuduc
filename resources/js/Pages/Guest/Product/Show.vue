<script setup>
import GuestPageLayout from '@/Layouts/GuestPageLayout.vue';
import ProductViewer360 from '@/Components/ProductViewer360.vue';
import SeoHead from '@/Components/SeoHead.vue';
import { Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';

const props = defineProps({ product: Object, relatedProducts: Array, seo: Object, jsonLd: [Object, Array] });

const normalImages = computed(() => (props.product.images || []).filter(i => !i.is_360));
const images360 = computed(() => (props.product.images || []).filter(i => i.is_360));
const selectedImage = ref(0);
const showReviewForm = ref(false);
const quantity = ref(1);

const formatPrice = (price) => {
    if (!price || price == 0) return 'Liên hệ';
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price);
};

const addToCart = () => {
    router.post(route('cart.add'), { product_id: props.product.id, quantity: quantity.value }, { preserveScroll: true });
};

const reviewForm = useForm({
    product_id: props.product.id,
    customer_name: '',
    customer_email: '',
    customer_phone: '',
    content: '',
    rating: 5,
});

const submitReview = () => {
    reviewForm.post(route('reviews.store'), {
        preserveScroll: true,
        onSuccess: () => { showReviewForm.value = false; reviewForm.reset(); },
    });
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
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
                <!-- Images -->
                <div class="space-y-4 reveal">
                    <ProductViewer360 v-if="images360.length" :images="images360" />
                    <div v-if="normalImages.length">
                        <div class="aspect-square bg-white border border-surface-border rounded-2xl overflow-hidden">
                            <img :src="'/storage/' + normalImages[selectedImage]?.image_path" :alt="product.name" class="w-full h-full object-contain" />
                        </div>
                        <div class="flex gap-2 mt-3">
                            <button v-for="(img, i) in normalImages" :key="img.id" @click="selectedImage = i"
                                :class="selectedImage === i ? 'ring-2 ring-brand-primary border-transparent' : 'border-surface-border'"
                                class="w-16 h-16 rounded-lg overflow-hidden border transition-all duration-300 bg-white">
                                <img :src="'/storage/' + img.image_path" class="w-full h-full object-cover" />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Product Info -->
                <div class="space-y-6 reveal" style="transition-delay:120ms">
                    <div>
                        <p v-if="product.sku" class="text-xs font-mono text-ink-light mb-2 tracking-widest uppercase">SKU: {{ product.sku }}</p>
                        <h1 class="text-3xl font-display font-bold text-ink-primary leading-tight">{{ product.name }}</h1>
                    </div>

                    <div class="font-display text-4xl font-bold" :class="product.price > 0 ? 'text-brand-hover' : 'text-ink-secondary'">
                        {{ formatPrice(product.price) }}
                    </div>

                    <!-- Add to cart -->
                    <div v-if="product.price > 0" class="flex items-center gap-4">
                        <div class="flex items-center border border-surface-border rounded-xl bg-surface-muted overflow-hidden">
                            <button @click="quantity > 1 && quantity--" class="px-4 py-3 text-ink-secondary hover:text-brand-hover hover:bg-black/[0.02] transition-colors">−</button>
                            <input v-model.number="quantity" type="number" min="1" class="w-14 text-center bg-transparent border-0 text-ink-primary focus:ring-0 font-display text-lg" />
                            <button @click="quantity++" class="px-4 py-3 text-ink-secondary hover:text-brand-hover hover:bg-black/[0.02] transition-colors">+</button>
                        </div>
                        <button @click="addToCart" class="btn-primary flex-1 flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                            Thêm vào giỏ hàng
                        </button>
                    </div>
                    <div v-else>
                        <a href="tel:1900xxxx" class="btn-primary inline-flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            Liên hệ báo giá
                        </a>
                    </div>

                    <!-- Specifications -->
                    <div v-if="product.specifications?.length" class="border-t border-surface-border pt-6">
                        <h3 class="text-sm font-display font-bold text-ink-primary uppercase tracking-wider mb-4">Thông số kỹ thuật</h3>
                        <div class="space-y-0 rounded-xl overflow-hidden border border-surface-border">
                            <div v-for="(spec, i) in product.specifications" :key="spec.key"
                                :class="i % 2 === 0 ? 'bg-surface-muted/50' : 'bg-white'"
                                class="flex text-sm border-b border-surface-border last:border-b-0">
                                <span class="w-2/5 py-2.5 px-4 text-ink-secondary font-medium">{{ spec.key }}</span>
                                <span class="w-3/5 py-2.5 px-4 text-ink-primary">{{ spec.value }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Description -->
            <div v-if="product.description" class="mt-14 storefront-card p-8 reveal">
                <h2 class="text-xl font-display font-bold text-ink-primary mb-4">Mô tả sản phẩm</h2>
                <div class="storefront-divider mb-6"></div>
                <div class="prose prose-p:text-ink-secondary prose-headings:font-display prose-a:text-brand-hover max-w-none" v-html="product.description"></div>
            </div>

            <!-- Reviews -->
            <div class="mt-14 reveal">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-display font-bold text-ink-primary">Đánh giá <span class="text-brand-hover">({{ product.approved_reviews?.length || 0 }})</span></h2>
                    <button @click="showReviewForm = !showReviewForm" class="btn-outline text-sm">Viết đánh giá</button>
                </div>

                <Transition enter-active-class="transition-all duration-300" enter-from-class="opacity-0 -translate-y-4" enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition-all duration-200" leave-from-class="opacity-100" leave-to-class="opacity-0">
                    <div v-if="showReviewForm" class="storefront-card p-6 mb-6">
                        <form @submit.prevent="submitReview" class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <input v-model="reviewForm.customer_name" type="text" placeholder="Họ tên *" required class="w-full px-4 py-2.5 bg-white border border-surface-border rounded-xl text-sm text-ink-primary placeholder-ink-light focus:outline-none focus:border-brand-primary focus:ring-1 focus:ring-brand-primary/20 transition" />
                                <input v-model="reviewForm.customer_email" type="email" placeholder="Email" class="w-full px-4 py-2.5 bg-white border border-surface-border rounded-xl text-sm text-ink-primary placeholder-ink-light focus:outline-none focus:border-brand-primary focus:ring-1 focus:ring-brand-primary/20 transition" />
                                <input v-model="reviewForm.customer_phone" type="text" placeholder="Số điện thoại" class="w-full px-4 py-2.5 bg-white border border-surface-border rounded-xl text-sm text-ink-primary placeholder-ink-light focus:outline-none focus:border-brand-primary focus:ring-1 focus:ring-brand-primary/20 transition" />
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-sm text-ink-secondary">Đánh giá:</span>
                                <button v-for="i in 5" :key="i" type="button" @click="reviewForm.rating = i" class="text-xl transition-colors" :class="i <= reviewForm.rating ? 'text-amber-400' : 'text-slate-300'">★</button>
                            </div>
                            <textarea v-model="reviewForm.content" rows="3" placeholder="Nội dung đánh giá *" required class="w-full px-4 py-2.5 bg-white border border-surface-border rounded-xl text-sm text-ink-primary placeholder-ink-light focus:outline-none focus:border-brand-primary focus:ring-1 focus:ring-brand-primary/20 transition"></textarea>
                            <button type="submit" :disabled="reviewForm.processing" class="btn-primary text-sm disabled:opacity-50">Gửi đánh giá</button>
                        </form>
                    </div>
                </Transition>

                <div class="space-y-3">
                    <div v-for="review in product.approved_reviews" :key="review.id" class="storefront-card p-4">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-8 h-8 rounded-full bg-brand-primary/10 flex items-center justify-center text-brand-hover text-xs font-display font-bold">{{ review.customer_name?.charAt(0) }}</div>
                            <span class="font-medium text-sm text-ink-primary">{{ review.customer_name }}</span>
                            <span class="text-amber-400 text-xs">{{ '★'.repeat(review.rating) }}<span class="text-slate-300">{{ '★'.repeat(5 - review.rating) }}</span></span>
                            <span class="text-xs text-ink-light ml-auto">{{ new Date(review.created_at).toLocaleDateString('vi-VN') }}</span>
                        </div>
                        <p class="text-sm text-ink-secondary pl-11">{{ review.content }}</p>
                    </div>
                    <div v-if="!product.approved_reviews?.length" class="text-center py-12 text-ink-light">
                        <svg class="w-12 h-12 mx-auto mb-3 text-ink-light" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="0.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        Chưa có đánh giá nào. Hãy là người đầu tiên!
                    </div>
                </div>
            </div>

            <!-- Related Products -->
            <div v-if="relatedProducts?.length" class="mt-14 reveal">
                <p class="section-tag">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Gợi ý cho bạn
                </p>
                <h2 class="section-title text-2xl">Sản phẩm <span class="text-brand-hover">liên quan</span></h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-5 mt-6">
                    <Link v-for="rp in relatedProducts" :key="rp.id" :href="route('products.show', rp.slug)" class="group product-card">
                        <div class="product-img-wrap aspect-square bg-surface-muted border-b border-surface-border">
                            <img v-if="rp.images?.length" :src="'/storage/' + rp.images[0].image_path" :alt="rp.name" />
                        </div>
                        <div class="p-4">
                            <h3 class="text-xs font-medium text-ink-primary group-hover:text-brand-hover transition-colors line-clamp-2">{{ rp.name }}</h3>
                            <p class="mt-2 font-display font-bold text-sm" :class="rp.price > 0 ? 'text-brand-hover' : 'text-ink-secondary'">{{ formatPrice(rp.price) }}</p>
                        </div>
                        <div class="h-0.5 bg-gradient-to-r from-brand-primary to-orange-500 scale-x-0 group-hover:scale-x-100 transition-transform duration-500 origin-left"></div>
                    </Link>
                </div>
            </div>
        </div>
    </GuestPageLayout>
</template>
