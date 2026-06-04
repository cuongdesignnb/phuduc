<script setup>
import SeoHead from '@/Components/SeoHead.vue';
import GuestPageLayout from '@/Layouts/GuestPageLayout.vue';
import { Link } from '@inertiajs/vue3';
import { computed, onMounted } from 'vue';

const props = defineProps({
    featuredProducts: Array,
    latestPosts: Array,
    settings: Object,
    homeSections: Object,
    canLogin: Boolean,
    canRegister: Boolean,
    seo: Object,
    jsonLd: [Object, Array],
});

const looksBrokenUtf8 = (value) => {
    if (typeof value !== 'string') return false;
    const codes = Array.from(value).map((char) => char.charCodeAt(0));

    return codes.some((code) => [0xc2, 0xc3, 0xc4, 0xc6, 0xca, 0xfffd].includes(code))
        || codes.some((code) => code >= 0x80 && code <= 0x9f)
        || codes.some((code, index) => code === 0xe1 && [0xba, 0xbb].includes(codes[index + 1]));
};

const fixText = (value) => {
    if (typeof value !== 'string' || !looksBrokenUtf8(value)) return value;

    try {
        const bytes = Uint8Array.from(Array.from(value), (char) => char.charCodeAt(0) & 255);
        return new TextDecoder('utf-8', { fatal: false }).decode(bytes);
    } catch {
        return value;
    }
};

const fixObject = (object = {}) => Object.fromEntries(
    Object.entries(object || {}).map(([key, value]) => [fixText(key), fixText(value)]),
);

const siteSettings = computed(() => fixObject(props.settings || {}));

const setting = (key, fallback = '') => {
    const value = siteSettings.value[key];
    return value === undefined || value === null || value === '' ? fallback : fixText(value);
};

const section = (key) => props.homeSections?.[key] || null;

const sectionItems = (key) => (section(key)?.items || []).map((item) => ({
    ...item,
    title: fixText(item.title),
    subtitle: fixText(item.subtitle),
    description: fixText(item.description),
    metadata_json: item.metadata_json || {},
}));

const sectionTitle = (key, fallback) => fixText(section(key)?.title) || fallback;

const products = computed(() => (props.featuredProducts || []).map((product) => ({
    ...product,
    displayName: fixText(product.name),
    displayDescription: fixText(product.description),
    displaySpecifications: fixObject(product.specifications || {}),
})));

const posts = computed(() => (props.latestPosts || []).map((post) => ({
    ...post,
    displayTitle: fixText(post.title),
    displaySummary: fixText(post.summary),
    displayCategory: fixText(post.category?.name),
})));

const ui = computed(() => ({
    heroTitle: setting('home.hero_title', 'GI\u1ea2I PH\u00c1P XE \u0110I\u1ec6N &\nTHI\u1ebeT B\u1eca \u0110I\u1ec6N C\u00d4NG NGHI\u1ec6P'),
    heroLead: setting('home.hero_subtitle', 'Hi\u1ec7u su\u1ea5t m\u1ea1nh m\u1ebd - V\u1eadn h\u00e0nh b\u1ec1n b\u1ec9 - Ti\u1ebft ki\u1ec7m n\u0103ng l\u01b0\u1ee3ng - Th\u00e2n thi\u1ec7n m\u00f4i tr\u01b0\u1eddng'),
    operate: 'V\u1eadn h\u00e0nh m\u1ea1nh m\u1ebd',
    operateSub: 'Hi\u1ec7u su\u1ea5t v\u01b0\u1ee3t tr\u1ed9i',
    saving: 'Ti\u1ebft ki\u1ec7m n\u0103ng l\u01b0\u1ee3ng',
    savingSub: 'Gi\u1ea3m chi ph\u00ed t\u1ed1i \u0111a',
    safe: 'An to\u00e0n & b\u1ec1n b\u1ec9',
    safeSub: '\u0110\u1ea1t chu\u1ea9n qu\u1ed1c t\u1ebf',
    viewProducts: setting('home.hero_primary_label', 'Xem s\u1ea3n ph\u1ea9m'),
    viewProductsUrl: setting('home.hero_primary_url', '/san-pham'),
    consult: setting('home.hero_secondary_label', 'T\u01b0 v\u1ea5n ngay'),
    featured: setting('home.featured_products_title', 'S\u1ea3n ph\u1ea9m n\u1ed5i b\u1eadt'),
    viewAll: 'Xem t\u1ea5t c\u1ea3',
    load: 'T\u1ea3i tr\u1ecdng',
    range: 'Qu\u00e3ng \u0111\u01b0\u1eddng',
    seats: 'S\u1ed1 ch\u1ed7 ng\u1ed3i',
    detail: 'Xem chi ti\u1ebft',
    contactPrice: 'Li\u00ean h\u1ec7',
    greenEnergy: setting('home.energy_eyebrow', 'N\u0103ng l\u01b0\u1ee3ng xanh'),
    future: setting('home.energy_title', 'Cho t\u01b0\u01a1ng lai b\u1ec1n v\u1eefng'),
    greenCopy: setting('home.energy_description', 'S\u1ea3n ph\u1ea9m xe \u0111i\u1ec7n & thi\u1ebft b\u1ecb \u0111i\u1ec7n c\u00f4ng nghi\u1ec7p gi\u00fap doanh nghi\u1ec7p t\u1ed1i \u01b0u chi ph\u00ed v\u1eadn h\u00e0nh.'),
    energySaving: setting('home.energy_stat_1_label', 'Ti\u1ebft ki\u1ec7m n\u0103ng l\u01b0\u1ee3ng'),
    energySavingValue: setting('home.energy_stat_1_value', '30-50%'),
    emission: setting('home.energy_stat_2_label', 'Gi\u1ea3m ph\u00e1t th\u1ea3i CO\u2082'),
    emissionValue: setting('home.energy_stat_2_value', '> 60%'),
    sectors: sectionTitle('industry_solutions', 'Gi\u1ea3i ph\u00e1p theo ng\u00e0nh'),
    testimonialTitle: sectionTitle('testimonials', 'Kh\u00e1ch h\u00e0ng n\u00f3i v\u1ec1 ch\u00fang t\u00f4i'),
    news: setting('home.latest_posts_title', 'Tin t\u1ee9c n\u1ed5i b\u1eadt'),
    partners: sectionTitle('partners', '\u0110\u1ed1i t\u00e1c ti\u00eau bi\u1ec3u'),
    consulting: sectionTitle('consultation_steps', 'T\u01b0 v\u1ea5n gi\u1ea3i ph\u00e1p \u0111\u00fang nhu c\u1ea7u'),
}));

const categories = computed(() => sectionItems('category_cards').map((item) => ({
    label: item.title,
    caption: item.subtitle || 'Xem ngay',
    kind: item.metadata_json?.tone || item.icon || 'cart',
    href: item.url || route('products.index'),
})));

const benefits = computed(() => sectionItems('benefits').map((item) => ({
    title: item.title,
    icon: item.icon || 'shield',
})));

const sectors = computed(() => sectionItems('industry_solutions').map((item) => ({
    title: item.title,
    tone: item.metadata_json?.tone || 'warehouse',
    image: item.image,
    href: item.url || route('products.index'),
})));

const testimonials = computed(() => sectionItems('testimonials'));
const partners = computed(() => sectionItems('partners'));
const consultationSteps = computed(() => sectionItems('consultation_steps'));
const heroImage = computed(() => setting('home.hero_image'));

const formatPrice = (price) => {
    const amount = Number(price || 0);
    if (!amount) return ui.value.contactPrice;
    return new Intl.NumberFormat('vi-VN').format(amount) + '\u0111';
};

const assetUrl = (path) => path ? `/storage/${path}` : '';

const specLine = (product, keys, fallback) => {
    const specs = product.displaySpecifications || {};
    const found = keys.map((key) => specs[key]).find(Boolean);
    return found || fallback;
};

onMounted(() => {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.12 });

    document.querySelectorAll('.pd-reveal').forEach((element) => observer.observe(element));
});
</script>

<template>
    <SeoHead v-bind="seo" :json-ld="jsonLd" />

    <GuestPageLayout>
        <section class="pd-hero overflow-hidden border-b border-[#e6e9ef] bg-[#f7f8fa]">
            <div class="relative mx-auto grid max-w-[1780px] gap-8 px-5 py-9 lg:grid-cols-[0.9fr_1.35fr] lg:px-8 lg:py-11">
                <div class="relative z-10 pd-reveal">
                    <h1 class="max-w-[700px] whitespace-pre-line text-[38px] font-black uppercase leading-[0.98] tracking-tight text-[#282c34] sm:text-[52px] lg:text-[58px] xl:text-[64px]">{{ ui.heroTitle }}</h1>
                    <p class="mt-5 text-lg font-medium text-[#555d69]">{{ ui.heroLead }}</p>

                    <div class="mt-6 grid max-w-[620px] gap-3 sm:grid-cols-3">
                        <div class="pd-pill">
                            <svg class="h-8 w-8 text-[#ffc400]" fill="currentColor" viewBox="0 0 24 24"><path d="m13 2-9 12h7l-1 8 9-13h-7l1-7Z"/></svg>
                            <span><strong>{{ ui.operate }}</strong>{{ ui.operateSub }}</span>
                        </div>
                        <div class="pd-pill">
                            <svg class="h-8 w-8 text-[#ffc400]" fill="currentColor" viewBox="0 0 24 24"><path d="M21 3C12 3 5 9 5 17c0 2.2 1.8 4 4 4 8 0 12-9 12-18ZM3 21c1.8-5.3 5.5-8.8 11-11"/></svg>
                            <span><strong>{{ ui.saving }}</strong>{{ ui.savingSub }}</span>
                        </div>
                        <div class="pd-pill">
                            <svg class="h-8 w-8 text-[#ffc400]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3 5 6v5c0 4.55 2.91 8.44 7 9.8 4.09-1.36 7-5.25 7-9.8V6l-7-3Z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 12 2 2 4-5"/></svg>
                            <span><strong>{{ ui.safe }}</strong>{{ ui.safeSub }}</span>
                        </div>
                    </div>

                    <div class="mt-7 flex flex-wrap gap-4">
                        <Link :href="ui.viewProductsUrl" class="pd-btn-primary">{{ ui.viewProducts }} <span>&rarr;</span></Link>
                        <a :href="`tel:${siteSettings['site.phone'] || '1800888688'}`" class="pd-btn-secondary">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106a1.125 1.125 0 0 0-1.173.417l-.97 1.293a1.125 1.125 0 0 1-1.21.38 12.035 12.035 0 0 1-7.143-7.143 1.125 1.125 0 0 1 .38-1.21l1.293-.97c.356-.267.52-.723.417-1.173L6.963 3.102A1.125 1.125 0 0 0 5.872 2.25H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/></svg>
                            {{ ui.consult }}
                        </a>
                    </div>
                </div>

                <div class="relative min-h-[360px] pd-reveal">
                    <img v-if="heroImage" :src="assetUrl(heroImage)" :alt="ui.heroTitle" class="absolute inset-0 h-full w-full object-contain" />
                    <template v-else>
                        <div class="pd-city"></div>
                        <div class="pd-machine pd-machine-cart"><div class="pd-cart-body"></div></div>
                        <div class="pd-machine pd-machine-forklift"><div class="pd-forklift-mast"></div><div class="pd-forklift-body"></div></div>
                        <div class="pd-machine pd-machine-dumper"><div class="pd-dumper-tray"></div><div class="pd-dumper-base"></div></div>
                        <div class="pd-machine pd-machine-excavator"><div class="pd-excavator-arm"></div><div class="pd-excavator-body"></div><div class="pd-excavator-track"></div></div>
                    </template>
                    <div class="absolute bottom-0 left-0 right-0 h-8 rounded-[50%] bg-black/10 blur-xl"></div>
                </div>

                <div class="absolute bottom-4 left-1/2 hidden -translate-x-1/2 gap-2 lg:flex">
                    <span class="h-2.5 w-8 rounded-full bg-[#ffd400]"></span><span class="h-2.5 w-8 rounded-full bg-[#b9bdc4]"></span><span class="h-2.5 w-8 rounded-full bg-[#b9bdc4]"></span><span class="h-2.5 w-2.5 rounded-full bg-[#747b86]"></span>
                </div>
            </div>
        </section>

        <section v-if="categories.length || benefits.length" class="mx-auto -mt-2 grid max-w-[1780px] gap-4 px-5 lg:grid-cols-[1fr_1fr] lg:px-8">
            <div v-if="categories.length" class="grid gap-4 md:grid-cols-3">
                <Link v-for="category in categories" :key="category.label" :href="category.href" class="pd-category-card pd-reveal">
                    <div class="pd-mini-vehicle" :class="`pd-mini-${category.kind}`"></div>
                    <div><h3>{{ category.label }}</h3><span>{{ category.caption }} &rarr;</span></div>
                </Link>
            </div>
            <div v-if="benefits.length" class="pd-benefit-row pd-reveal">
                <div v-for="benefit in benefits" :key="benefit.title" class="pd-benefit">
                    <svg v-if="benefit.icon === 'shield'" class="h-9 w-9" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3 5 6v5c0 4.55 2.91 8.44 7 9.8 4.09-1.36 7-5.25 7-9.8V6l-7-3Z"/></svg>
                    <svg v-else-if="benefit.icon === 'award'" class="h-9 w-9" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15.75A6.75 6.75 0 1 0 12 2.25a6.75 6.75 0 0 0 0 13.5ZM8.25 14.25 6.75 21l5.25-3 5.25 3-1.5-6.75"/></svg>
                    <svg v-else-if="benefit.icon === 'truck'" class="h-9 w-9" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 6h11v10H3V6Zm11 4h3l4 4v2h-7v-6ZM7 19a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm10 0a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z"/></svg>
                    <svg v-else-if="benefit.icon === 'headset'" class="h-9 w-9" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 13v-1a8 8 0 0 1 16 0v1M4 13h3v6H5a1 1 0 0 1-1-1v-5Zm13 0h3v5a1 1 0 0 1-1 1h-2v-6Zm0 6c0 1.5-1.5 2-3.5 2H12"/></svg>
                    <svg v-else class="h-9 w-9" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m3 7 9-4 9 4-9 4-9-4Zm0 0v10l9 4 9-4V7M12 11v10"/></svg>
                    <strong>{{ benefit.title }}</strong>
                </div>
            </div>
        </section>

        <section class="pd-home-main mx-auto grid max-w-[1780px] items-start gap-5 px-5 py-5 lg:grid-cols-[1.02fr_0.98fr] lg:px-8">
            <div class="pd-side-stack">
                <div class="pd-panel pd-fit-panel pd-reveal">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="pd-section-title">{{ ui.featured }}</h2>
                        <Link :href="route('products.index')" class="pd-see-all">{{ ui.viewAll }} <span>&rarr;</span></Link>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        <article v-for="(product, index) in products" :key="product.id" class="pd-product-card" :style="{ transitionDelay: `${index * 80}ms` }">
                            <button class="absolute right-4 top-4 z-10 grid h-8 w-8 place-items-center rounded-full border border-[#dfe3e8] bg-white text-[#a5acb6] transition hover:text-[#e2a800]">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.936 0-3.6 1.126-4.312 2.733-.712-1.607-2.376-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/></svg>
                            </button>
                            <Link :href="route('products.show', product.slug)" class="block">
                                <div class="pd-product-image">
                                    <img v-if="product.images?.length" :src="assetUrl(product.images[0].image_path)" :alt="product.displayName" />
                                    <div v-else class="pd-fallback-vehicle" :class="index % 2 ? 'pd-fallback-forklift' : 'pd-fallback-cart'"></div>
                                </div>
                                <h3 class="mt-3 min-h-[42px] text-[15px] font-extrabold leading-snug text-[#252a33]">{{ product.displayName }}</h3>
                            </Link>
                            <ul class="mt-2 space-y-1 text-[12px] leading-5 text-[#646c78]">
                                <li>&bull; {{ ui.load }}: {{ specLine(product, [ui.load, 'T\u1ea3i tr\u1ecdng n\u00e2ng', ui.seats], '8 ng\u01b0\u1eddi') }}</li>
                                <li>&bull; {{ ui.range }}: {{ specLine(product, ['Ph\u1ea1m vi'], '80-100 km') }}</li>
                                <li>&bull; Pin: {{ specLine(product, ['Pin'], 'Lithium 80V/250Ah') }}</li>
                            </ul>
                            <div class="mt-3 flex items-center gap-1 text-[#ffc400]"><span v-for="star in 5" :key="star">&#9733;</span><span class="ml-1 text-xs text-[#7b828e]">({{ 18 + index * 9 }})</span></div>
                            <div class="mt-2 text-[16px] font-black text-[#20242c]">{{ formatPrice(product.price) }}</div>
                            <div class="mt-3 flex gap-2">
                                <Link :href="route('products.show', product.slug)" class="flex-1 rounded-md bg-[#ffd400] py-2.5 text-center text-xs font-black text-[#1b1d22] transition hover:bg-[#f2c500]">{{ ui.detail }}</Link>
                                <Link :href="route('cart.add')" method="post" :data="{ product_id: product.id, quantity: 1 }" as="button" preserve-scroll class="grid h-9 w-10 place-items-center rounded-md border border-[#dfe3e8] bg-white text-[#5d6570] transition hover:border-[#ffd400] hover:text-[#d49d00]">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437m0 0L6.75 14.25h10.5l3-8.978H5.106ZM8.25 20.25h.008v.008H8.25v-.008Zm9 0h.008v.008h-.008v-.008Z"/></svg>
                                </Link>
                            </div>
                        </article>
                    </div>
                </div>

                <div v-if="partners.length" class="pd-panel pd-fit-panel pd-reveal">
                    <h2 class="pd-section-title">{{ ui.partners }}</h2>
                    <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
                        <a v-for="partner in partners" :key="partner.id || partner.title" :href="partner.url || '#'" class="grid h-20 place-items-center rounded-lg border border-[#e4e7ed] bg-white px-3 text-center text-sm font-black text-[#506176]">
                            <img v-if="partner.image" :src="assetUrl(partner.image)" :alt="partner.title" class="max-h-12 max-w-full object-contain" />
                            <span v-else>{{ partner.title }}</span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="pd-side-stack">
                <div class="pd-energy-card pd-reveal">
                    <div><p>{{ ui.greenEnergy }}</p><h2>{{ ui.future }}</h2><span>{{ ui.greenCopy }}</span></div>
                    <div class="pd-leaf"><svg viewBox="0 0 140 140" fill="none"><path d="M120 15C72 18 27 43 20 90c-4 27 15 43 40 36 46-12 61-64 60-111Z" fill="#ffd400"/><path d="M38 107c23-36 44-53 76-73M70 40l7 35 33 4M49 65l7 26 25 4" stroke="white" stroke-width="8" stroke-linecap="round"/></svg></div>
                    <div class="pd-energy-stats"><strong>{{ ui.energySaving }}<br><b>{{ ui.energySavingValue }}</b></strong><strong>{{ ui.emission }}<br><b>{{ ui.emissionValue }}</b></strong></div>
                </div>

                <div v-if="sectors.length" class="pd-panel pd-fit-panel pd-reveal">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="pd-section-title">{{ ui.sectors }}</h2>
                        <Link :href="route('products.index')" class="pd-see-all">{{ ui.viewAll }} <span>&rarr;</span></Link>
                    </div>
                    <div class="grid grid-cols-2 gap-3 md:grid-cols-5">
                        <Link v-for="sector in sectors" :key="sector.title" :href="sector.href" class="pd-sector">
                            <div :class="`pd-sector-image pd-sector-${sector.tone}`">
                                <img v-if="sector.image" :src="assetUrl(sector.image)" :alt="sector.title" />
                            </div><strong>{{ sector.title }}</strong>
                        </Link>
                    </div>
                </div>

                <div class="grid items-start gap-5 lg:grid-cols-[0.92fr_1.08fr]">
                    <div v-if="testimonials.length" class="pd-panel pd-fit-panel pd-reveal">
                        <h2 class="pd-section-title">{{ ui.testimonialTitle }}</h2>
                        <div v-for="testimonial in testimonials.slice(0, 1)" :key="testimonial.id || testimonial.title" class="mt-4 flex gap-4">
                            <div class="grid h-16 w-16 shrink-0 place-items-center overflow-hidden rounded-full bg-[#dfe6f0] text-2xl font-black text-[#415267]">
                                <img v-if="testimonial.image" :src="assetUrl(testimonial.image)" :alt="testimonial.title" class="h-full w-full object-cover" />
                                <span v-else>{{ testimonial.metadata_json?.avatar_text || testimonial.title?.charAt(0) || 'A' }}</span>
                            </div>
                            <div><p class="text-sm leading-6 text-[#59616e]">"{{ testimonial.description }}"</p><strong class="mt-3 block text-sm">{{ testimonial.title }}</strong><span class="text-xs text-[#7b828e]">{{ testimonial.subtitle }}</span></div>
                        </div>
                    </div>
                    <div class="pd-panel pd-fit-panel pd-reveal">
                        <div class="mb-4 flex items-center justify-between">
                            <h2 class="pd-section-title">{{ ui.news }}</h2>
                            <Link :href="route('news.index')" class="pd-see-all">{{ ui.viewAll }} <span>&rarr;</span></Link>
                        </div>
                        <div class="grid gap-3 md:grid-cols-3">
                            <Link v-for="post in posts" :key="post.id" :href="route('news.show', post.slug)" class="pd-news-card">
                                <div class="pd-news-image"><img v-if="post.featured_image" :src="assetUrl(post.featured_image)" :alt="post.displayTitle" /><div v-else class="pd-news-fallback"></div></div>
                                <h3>{{ post.displayTitle }}</h3><time>{{ new Date(post.created_at).toLocaleDateString('vi-VN') }}</time>
                            </Link>
                        </div>
                    </div>
                </div>

                <div v-if="consultationSteps.length" class="pd-panel pd-fit-panel pd-reveal">
                    <h2 class="pd-section-title">{{ ui.consulting }}</h2>
                    <div class="mt-4 grid gap-4 md:grid-cols-3">
                        <div v-for="step in consultationSteps" :key="step.id || step.title" class="rounded-lg p-4" :class="{
                            'bg-[#fff8d9]': step.metadata_json?.tone !== 'blue' && step.metadata_json?.tone !== 'green',
                            'bg-[#eef5ff]': step.metadata_json?.tone === 'blue',
                            'bg-[#effaf2]': step.metadata_json?.tone === 'green',
                        }"><strong class="block text-lg">{{ step.title }}</strong><span class="text-sm text-[#59616e]">{{ step.description }}</span></div>
                    </div>
                </div>
            </div>
        </section>
    </GuestPageLayout>
</template>

<style scoped>
.pd-reveal { opacity: 0; transform: translateY(22px); transition: opacity .7s ease, transform .7s ease; }
.pd-reveal.is-visible { opacity: 1; transform: translateY(0); }
.pd-hero { background: radial-gradient(circle at 72% 42%, rgba(255, 212, 0, .18), transparent 24%), linear-gradient(115deg, rgba(255,255,255,.96), rgba(246,247,249,.88)), repeating-linear-gradient(135deg, rgba(120,128,140,.08) 0 1px, transparent 1px 42px); }
.pd-pill { display: flex; align-items: center; gap: 10px; min-height: 66px; padding: 10px 12px; border: 1px solid #e2e6ec; border-radius: 8px; background: rgba(255,255,255,.86); box-shadow: 0 8px 18px rgba(31, 41, 55, .05); }
.pd-pill strong { display: block; font-size: 12px; font-weight: 900; text-transform: uppercase; }
.pd-pill span { font-size: 12px; line-height: 1.35; color: #5d6570; }
.pd-btn-primary, .pd-btn-secondary { display: inline-flex; align-items: center; justify-content: center; gap: 10px; height: 48px; padding: 0 28px; border-radius: 7px; font-size: 15px; font-weight: 900; transition: transform .25s ease, box-shadow .25s ease, background .25s ease; }
.pd-btn-primary { background: #ffd400; color: #16181d; box-shadow: 0 12px 22px rgba(255, 196, 0, .25); }
.pd-btn-secondary { border: 1px solid #d8dde5; background: #fff; color: #303640; }
.pd-btn-primary:hover, .pd-btn-secondary:hover { transform: translateY(-2px); }
.pd-city { position: absolute; inset: 0 0 74px; opacity: .45; background: linear-gradient(to top, rgba(170,180,194,.42), rgba(170,180,194,0) 62%), repeating-linear-gradient(90deg, transparent 0 34px, rgba(154,164,179,.5) 34px 42px, transparent 42px 62px); clip-path: polygon(0 100%, 0 80%, 5% 80%, 5% 58%, 11% 58%, 11% 72%, 18% 72%, 18% 42%, 25% 42%, 25% 64%, 33% 64%, 33% 25%, 42% 25%, 42% 56%, 49% 56%, 49% 34%, 56% 34%, 56% 67%, 65% 67%, 65% 48%, 72% 48%, 72% 70%, 80% 70%, 80% 38%, 88% 38%, 88% 62%, 96% 62%, 96% 83%, 100% 83%, 100% 100%); }
.pd-machine { position: absolute; bottom: 22px; animation: pd-float 4.8s ease-in-out infinite; }
.pd-machine-cart { left: 1%; width: 28%; height: 42%; animation-delay: .2s; }
.pd-machine-forklift { left: 31%; width: 29%; height: 63%; animation-delay: .45s; }
.pd-machine-dumper { left: 61%; width: 20%; height: 33%; animation-delay: .6s; }
.pd-machine-excavator { right: 0; width: 24%; height: 66%; animation-delay: .8s; }
.pd-cart-body, .pd-forklift-body, .pd-dumper-base, .pd-excavator-body { position: absolute; bottom: 0; border: 6px solid #16181d; border-radius: 14px 14px 7px 7px; background: #fff; box-shadow: inset 0 -18px 0 #eceff3; }
.pd-cart-body { left: 0; right: 0; height: 46%; }
.pd-forklift-body { left: 18%; right: 3%; height: 40%; background: #ffd400; box-shadow: inset 0 -18px 0 #1b1d22; }
.pd-forklift-mast { position: absolute; bottom: 3%; left: 3%; height: 92%; width: 14%; border: 7px solid #16181d; border-radius: 8px; }
.pd-dumper-base { left: 16%; right: 4%; height: 36%; background: #1b1d22; }
.pd-dumper-tray { position: absolute; bottom: 30%; left: 0; width: 88%; height: 48%; border-radius: 0 0 18px 18px; background: #ffd400; transform: skewX(-13deg); border: 5px solid #d9a600; }
.pd-excavator-body { right: 5%; bottom: 12%; width: 58%; height: 35%; background: #ffd400; }
.pd-excavator-track { position: absolute; bottom: 0; right: 0; width: 78%; height: 17%; border-radius: 999px; background: #16181d; box-shadow: inset 0 -7px 0 #474c55; }
.pd-excavator-arm { position: absolute; top: 5%; left: 2%; width: 80%; height: 13%; border-radius: 999px; background: #ffd400; transform: rotate(-36deg); transform-origin: right center; box-shadow: 46px 45px 0 -4px #ffd400; }
.pd-cart-body::before, .pd-forklift-body::before, .pd-dumper-base::before, .pd-excavator-body::before { content: ""; position: absolute; bottom: -20px; left: 14%; width: 28px; height: 28px; border: 7px solid #15171c; border-radius: 999px; background: #f7f8fa; box-shadow: 92px 0 0 -7px #f7f8fa, 92px 0 0 0 #15171c; }
@keyframes pd-float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-9px); } }
.pd-category-card, .pd-benefit-row, .pd-panel, .pd-energy-card { border: 1px solid #e0e4eb; border-radius: 13px; background: rgba(255,255,255,.94); box-shadow: 0 12px 28px rgba(31, 41, 55, .06); }
.pd-home-main > * { min-width: 0; }
.pd-side-stack { display: grid; align-content: start; gap: 20px; min-width: 0; }
.pd-fit-panel { align-self: start; height: fit-content; }
.pd-category-card { display: flex; align-items: center; min-height: 108px; gap: 18px; padding: 14px 20px; transition: transform .25s ease, border-color .25s ease; }
.pd-category-card:hover { transform: translateY(-3px); border-color: #ffd400; }
.pd-category-card h3 { font-size: 17px; font-weight: 900; text-transform: uppercase; }
.pd-category-card span { margin-top: 8px; display: inline-flex; align-items: center; gap: 5px; font-size: 13px; color: #8a929d; }
.pd-mini-vehicle { width: 108px; height: 72px; border-radius: 10px; background: linear-gradient(160deg, #fff 45%, #e7ebf0 46%); border: 4px solid #15171c; position: relative; }
.pd-mini-vehicle::after { content: ""; position: absolute; bottom: -15px; left: 14px; width: 22px; height: 22px; border: 6px solid #15171c; border-radius: 50%; background: #fff; box-shadow: 56px 0 0 -6px #fff, 56px 0 0 0 #15171c; }
.pd-mini-crane { border-color: #d9a600; background: #ffd400; transform: scale(.82); }
.pd-mini-forklift { background: linear-gradient(160deg, #ffd400 50%, #15171c 51%); }
.pd-benefit-row { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); min-height: 108px; }
.pd-benefit { display: flex; align-items: center; justify-content: center; gap: 12px; border-right: 1px solid #e8ebf0; color: #f0b400; }
.pd-benefit:last-child { border-right: 0; }
.pd-benefit strong { max-width: 110px; font-size: 13px; font-weight: 900; line-height: 1.3; text-transform: uppercase; color: #303640; }
.pd-panel { padding: 20px 22px; }
.pd-section-title { font-size: 20px; font-weight: 900; text-transform: uppercase; color: #252a33; }
.pd-see-all { display: inline-flex; gap: 6px; align-items: center; color: #edb200; font-size: 14px; font-weight: 900; }
.pd-product-card { position: relative; border: 1px solid #e4e8ee; border-radius: 10px; background: #fff; padding: 14px; transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease; }
.pd-product-card:hover { transform: translateY(-5px); border-color: #ffd400; box-shadow: 0 18px 28px rgba(31, 41, 55, .12); }
.pd-product-image { display: grid; place-items: center; aspect-ratio: 1.25; overflow: hidden; border-radius: 8px; background: linear-gradient(#fff, #f2f4f7); }
.pd-product-image img { width: 100%; height: 100%; object-fit: contain; transition: transform .4s ease; }
.pd-product-card:hover .pd-product-image img, .pd-product-card:hover .pd-fallback-vehicle { transform: scale(1.06); }
.pd-fallback-vehicle { width: 78%; height: 52%; position: relative; border: 6px solid #171a20; border-radius: 12px 12px 7px 7px; background: #fff; transition: transform .4s ease; }
.pd-fallback-forklift { background: #ffd400; box-shadow: inset 0 -20px 0 #171a20; }
.pd-fallback-vehicle::after { content: ""; position: absolute; bottom: -19px; left: 12%; width: 28px; height: 28px; border: 7px solid #171a20; border-radius: 999px; background: #fff; box-shadow: 92px 0 0 -7px #fff, 92px 0 0 0 #171a20; }
.pd-energy-card { min-height: 164px; display: grid; grid-template-columns: 1fr 220px 190px; align-items: center; gap: 18px; padding: 22px 26px; overflow: hidden; background: radial-gradient(circle at 66% 54%, rgba(255, 212, 0, .33), transparent 28%), linear-gradient(110deg, #fff, #f4f6f9); }
.pd-energy-card p { color: #e2a800; font-size: 24px; font-weight: 900; text-transform: uppercase; }
.pd-energy-card h2 { margin-top: 2px; font-size: 24px; font-weight: 950; text-transform: uppercase; }
.pd-energy-card span { display: block; margin-top: 12px; max-width: 560px; color: #59616e; font-size: 14px; line-height: 1.65; }
.pd-leaf { animation: pd-leaf-pulse 3s ease-in-out infinite; }
@keyframes pd-leaf-pulse { 0%, 100% { transform: scale(1) rotate(-3deg); } 50% { transform: scale(1.05) rotate(2deg); } }
.pd-energy-stats { display: grid; gap: 12px; }
.pd-energy-stats strong { border-radius: 9px; background: rgba(255,255,255,.82); padding: 13px 15px; color: #66707d; font-size: 13px; line-height: 1.45; }
.pd-energy-stats b { color: #20242c; font-size: 22px; }
.pd-sector { display: block; }
.pd-sector-image { height: 92px; border-radius: 8px; border: 1px solid #cbd2dc; overflow: hidden; background-size: cover; }
.pd-sector-image img { width: 100%; height: 100%; object-fit: cover; }
.pd-sector strong { display: block; margin-top: 8px; text-align: center; font-size: 12px; line-height: 1.3; }
.pd-sector-warehouse { background: linear-gradient(135deg, rgba(0,0,0,.25), rgba(0,0,0,0)), repeating-linear-gradient(90deg, #4b5563 0 26px, #ffd400 26px 38px, #313946 38px 64px); }
.pd-sector-factory { background: linear-gradient(135deg, #dfe5ec, #8e98a7), repeating-linear-gradient(0deg, transparent 0 18px, rgba(255,255,255,.42) 18px 22px); }
.pd-sector-site { background: linear-gradient(135deg, #b2c6d8, #f1c33e 48%, #9a6a31); }
.pd-sector-farm { background: linear-gradient(135deg, #8dc56f 0 38%, #d6edb7 39% 52%, #70a348 53%); }
.pd-sector-campus { background: linear-gradient(135deg, #e4e8ee, #8c96a5 55%, #ffd400 56%); }
.pd-news-card { display: block; }
.pd-news-image { height: 88px; border-radius: 8px; overflow: hidden; background: #eef1f5; }
.pd-news-image img { width: 100%; height: 100%; object-fit: cover; }
.pd-news-fallback { width: 100%; height: 100%; background: linear-gradient(135deg, #ffd400, #252a33); }
.pd-news-card h3 { margin-top: 8px; min-height: 38px; color: #252a33; font-size: 13px; font-weight: 800; line-height: 1.35; }
.pd-news-card time { color: #8a929d; font-size: 12px; }
@media (max-width: 1280px) { .pd-energy-card { grid-template-columns: 1fr; } .pd-benefit-row { grid-template-columns: repeat(2, minmax(0, 1fr)); } .pd-benefit { min-height: 92px; border-bottom: 1px solid #e8ebf0; } }
@media (max-width: 768px) { .pd-machine-cart { left: 0; width: 35%; } .pd-machine-forklift { left: 25%; width: 38%; } .pd-machine-dumper { display: none; } .pd-machine-excavator { right: -5%; width: 36%; } .pd-benefit-row { grid-template-columns: 1fr; } .pd-benefit { justify-content: flex-start; padding: 0 20px; border-right: 0; } .pd-energy-card p, .pd-energy-card h2 { font-size: 21px; } }
</style>
