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
    <footer class="border-t border-line bg-surface-muted">
        <StorefrontContainer>
            <div class="grid gap-10 py-12 sm:grid-cols-2 lg:grid-cols-12">
                <div class="sm:col-span-2 lg:col-span-4">
                    <Link :href="route('home')" class="inline-flex items-center gap-3">
                        <img v-if="site.logo_url" :src="site.logo_url" :alt="site.name || 'Trang chủ'" width="160" height="48" class="h-12 w-auto max-w-40 object-contain">
                        <strong class="font-display text-2xl font-bold uppercase text-content-primary">{{ site.name }}</strong>
                    </Link>
                    <p v-if="site.description" class="mt-4 max-w-md text-sm leading-6 text-content-secondary">{{ site.description }}</p>
                    <div v-if="socials.length" class="mt-5 flex flex-wrap gap-2">
                        <a v-for="social in socials" :key="social.network" :href="social.url" target="_blank" rel="noopener noreferrer" class="inline-flex min-h-11 items-center rounded-lg border border-line bg-surface-card px-4 text-sm font-semibold hover:border-brand-border" :aria-label="`${social.label} của ${site.name}`">{{ social.label }}</a>
                    </div>
                </div>

                <div class="lg:col-span-3">
                    <h2 class="font-display text-base font-bold uppercase tracking-wide">Liên hệ</h2>
                    <address class="mt-4 space-y-2 text-sm not-italic leading-6 text-content-secondary">
                        <p v-if="contactPhone"><a :href="`tel:${contactPhone}`" class="hover:text-brand-text">{{ contactPhone }}</a></p>
                        <p v-if="site.email"><a :href="`mailto:${site.email}`" class="break-all hover:text-brand-text">{{ site.email }}</a></p>
                        <p v-if="site.address">{{ site.address }}</p>
                        <p v-if="site.working_hours">{{ site.working_hours }}</p>
                    </address>
                </div>

                <div class="contents">
                    <section v-for="group in validGroups" :key="group.id || group.label" class="lg:col-span-2">
                        <h2 class="font-display text-base font-bold uppercase tracking-wide">{{ group.label }}</h2>
                        <ul class="mt-4 space-y-2 text-sm text-content-secondary">
                            <li v-for="child in group.children.filter((item) => item.url)" :key="child.id || child.label">
                                <component :is="linkComponent(child.url)" :href="child.url" :target="isExternal(child.url) ? '_blank' : undefined" :rel="isExternal(child.url) ? 'noopener noreferrer' : undefined" class="inline-flex min-h-8 items-center hover:text-brand-text">{{ child.label }}</component>
                            </li>
                        </ul>
                    </section>
                </div>

                <div v-if="!validGroups.length" class="lg:col-span-3">
                    <h2 class="font-display text-base font-bold uppercase tracking-wide">Hỗ trợ</h2>
                    <ul class="mt-4 space-y-2 text-sm text-content-secondary">
                        <li><Link :href="route('warranty-lookup.index')" class="inline-flex min-h-8 items-center hover:text-brand-text">Tra cứu bảo hành</Link></li>
                        <li><Link :href="route('order-lookup.index')" class="inline-flex min-h-8 items-center hover:text-brand-text">Tra cứu đơn hàng</Link></li>
                        <li><Link :href="route('products.index')" class="inline-flex min-h-8 items-center hover:text-brand-text">Sản phẩm</Link></li>
                    </ul>
                </div>
            </div>
        </StorefrontContainer>
        <div class="border-t border-line py-4 text-center text-xs text-content-muted">{{ site.copyright }}</div>
    </footer>
</template>
