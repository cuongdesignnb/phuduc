<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import StorefrontContainer from './StorefrontContainer.vue';

const props = defineProps({
    site: { type: Object, default: () => ({}) },
    groups: { type: Array, default: () => [] },
});
const contactPhone = computed(() => props.site.hotline || props.site.phone || '');
const validGroups = computed(() => props.groups.filter((group) => group.label && group.children?.some((child) => child.url)));
const socialLabels = { facebook: 'Facebook', zalo: 'Zalo', youtube: 'YouTube' };
const socials = computed(() => Object.entries(props.site.social_links || {})
    .filter(([, url]) => url)
    .map(([network, url]) => ({ network, url, label: socialLabels[network] || network })));
const isExternal = (url) => /^(https?:)?\/\//.test(url || '');
const linkComponent = (url) => (isExternal(url) ? 'a' : Link);
</script>

<template>
    <footer class="relative overflow-hidden border-t border-surface-inverse bg-surface-inverse text-content-inverse">
        <div class="pointer-events-none absolute inset-x-0 top-0 h-px bg-brand" aria-hidden="true" />
        <div class="pointer-events-none absolute -right-32 -top-32 h-80 w-80 rounded-full bg-brand/10 blur-3xl" aria-hidden="true" />
        <div class="pointer-events-none absolute -bottom-40 left-1/4 h-72 w-72 rounded-full bg-brand/5 blur-3xl" aria-hidden="true" />
        <StorefrontContainer class="relative">
            <div class="grid gap-10 py-14 sm:grid-cols-2 lg:grid-cols-12 lg:gap-8">
                <div class="sm:col-span-2 lg:col-span-4">
                    <Link :href="route('home')" class="inline-flex items-center gap-3">
                        <img v-if="site.logo_url" :src="site.logo_url" :alt="site.name || 'Trang chủ'" width="160" height="48" class="h-12 w-auto max-w-40 rounded-xl bg-surface-card p-2 object-contain">
                        <span v-else class="h-10 w-1 rounded-full bg-brand" aria-hidden="true" />
                        <span>
                            <strong class="block font-display text-2xl font-bold uppercase tracking-tight text-content-inverse">{{ site.name }}</strong>
                            <small v-if="site.tagline" class="mt-1 block text-[10px] font-bold uppercase tracking-[0.14em] text-content-inverse/55">{{ site.tagline }}</small>
                        </span>
                    </Link>
                    <p v-if="site.description" class="mt-5 max-w-md text-sm leading-6 text-content-inverse/70">{{ site.description }}</p>
                    <div class="mt-6 inline-flex items-center gap-2 rounded-full border border-content-inverse/15 bg-content-inverse/5 px-3 py-2 text-xs font-semibold text-content-inverse/80">
                        <span class="h-2 w-2 rounded-full bg-brand" aria-hidden="true" />Giải pháp thiết bị công nghiệp toàn quốc
                    </div>
                    <div v-if="socials.length" class="mt-5 flex flex-wrap gap-2">
                        <a v-for="social in socials" :key="social.network" :href="social.url" target="_blank" rel="noopener noreferrer" class="inline-flex min-h-10 items-center rounded-xl border border-content-inverse/15 bg-content-inverse/5 px-3.5 text-sm font-semibold text-content-inverse/85 transition hover:-translate-y-0.5 hover:border-brand hover:bg-brand hover:text-brand-contrast" :aria-label="`${social.label} của ${site.name}`">{{ social.label }}</a>
                    </div>
                </div>

                <div class="lg:col-span-3">
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-brand">Kết nối</p>
                    <h2 class="mt-2 font-display text-lg font-bold text-content-inverse">Liên hệ tư vấn</h2>
                    <address class="mt-5 space-y-3 text-sm not-italic leading-6 text-content-inverse/70">
                        <p v-if="contactPhone"><a :href="`tel:${contactPhone}`" class="inline-flex items-center gap-2 font-semibold text-content-inverse transition hover:text-brand"><span class="grid h-7 w-7 place-items-center rounded-lg bg-content-inverse/10 text-brand" aria-hidden="true"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106a1.125 1.125 0 0 0-1.173.417l-.97 1.293" /></svg></span>{{ contactPhone }}</a></p>
                        <p v-if="site.email"><a :href="`mailto:${site.email}`" class="inline-flex items-start gap-2 break-all transition hover:text-brand"><span class="grid h-7 w-7 shrink-0 place-items-center rounded-lg bg-content-inverse/10 text-brand" aria-hidden="true"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.75 6.75h16.5v10.5H3.75V6.75Zm0 0 8.25 5.625L20.25 6.75" /></svg></span><span>{{ site.email }}</span></a></p>
                        <p v-if="site.address">{{ site.address }}</p>
                        <p v-if="site.working_hours">{{ site.working_hours }}</p>
                    </address>
                </div>

                <div class="contents">
                    <section v-for="group in validGroups" :key="group.id || group.label" class="lg:col-span-2">
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-brand">Khám phá</p>
                        <h2 class="mt-2 font-display text-lg font-bold text-content-inverse">{{ group.label }}</h2>
                        <ul class="mt-5 space-y-2 text-sm text-content-inverse/70">
                            <li v-for="child in group.children.filter((item) => item.url)" :key="child.id || child.label">
                                <component :is="linkComponent(child.url)" :href="child.url" :target="isExternal(child.url) ? '_blank' : undefined" :rel="isExternal(child.url) ? 'noopener noreferrer' : undefined" class="inline-flex min-h-8 items-center transition hover:translate-x-0.5 hover:text-brand">{{ child.label }}</component>
                            </li>
                        </ul>
                    </section>
                </div>

                <div v-if="!validGroups.length" class="lg:col-span-3">
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-brand">Hỗ trợ</p>
                    <h2 class="mt-2 font-display text-lg font-bold text-content-inverse">Dịch vụ khách hàng</h2>
                    <ul class="mt-5 space-y-2 text-sm text-content-inverse/70">
                        <li><Link :href="route('warranty-lookup.index')" class="inline-flex min-h-8 items-center transition hover:translate-x-0.5 hover:text-brand">Tra cứu bảo hành</Link></li>
                        <li><Link :href="route('order-lookup.index')" class="inline-flex min-h-8 items-center transition hover:translate-x-0.5 hover:text-brand">Tra cứu đơn hàng</Link></li>
                        <li><Link :href="route('products.index')" class="inline-flex min-h-8 items-center transition hover:translate-x-0.5 hover:text-brand">Sản phẩm</Link></li>
                    </ul>
                </div>
            </div>
        </StorefrontContainer>
        <div class="relative border-t border-content-inverse/10">
            <StorefrontContainer class="flex min-h-16 flex-col justify-center gap-2 py-3 text-xs text-content-inverse/55 sm:flex-row sm:items-center sm:justify-between">
                <p>{{ site.copyright }}</p>
                <p>Chính hãng · Minh bạch cấu hình · Hỗ trợ kỹ thuật</p>
            </StorefrontContainer>
        </div>
    </footer>
</template>
