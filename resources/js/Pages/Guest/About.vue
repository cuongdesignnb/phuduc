<script setup>
import GuestPageLayout from '@/Layouts/GuestPageLayout.vue';
import SeoHead from '@/Components/SeoHead.vue';
import { onMounted } from 'vue';

defineProps({ settings: Object, seo: Object, jsonLd: [Object, Array] });

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
        <section class="relative h-72 md:h-[400px] overflow-hidden flex items-center justify-center">
            <div class="absolute inset-0 bg-grid opacity-20 pointer-events-none"></div>
            <div class="absolute inset-0 bg-gradient-to-b from-carbon-950/60 via-carbon-950/40 to-carbon-950"></div>
            <div class="absolute top-1/3 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[300px] bg-volt-500/[0.06] rounded-full blur-[120px] pointer-events-none"></div>
            <div class="relative z-10 text-center px-4">
                <p class="section-tag mx-auto">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    Phú Đức Electric Vehicle
                </p>
                <h1 class="text-4xl md:text-5xl font-display font-bold text-white mt-4">{{ settings?.['about.title'] || 'Về chúng tôi' }}</h1>
            </div>
        </section>

        <div class="neon-line"></div>

        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 py-16 space-y-16">
            <!-- About Content -->
            <section v-if="settings?.['about.content']" class="glass-card p-8 reveal">
                <div class="prose prose-invert prose-p:text-carbon-300 prose-headings:font-display prose-a:text-volt-400 max-w-none" v-html="settings['about.content']"></div>
            </section>

            <!-- Mission & Vision -->
            <section class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div v-if="settings?.['about.mission']" class="glass-card-hover p-8 reveal">
                    <div class="w-12 h-12 rounded-xl bg-volt-500/10 flex items-center justify-center mb-5">
                        <svg class="w-6 h-6 text-volt-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <h3 class="text-lg font-display font-bold text-white mb-3">Sứ mệnh</h3>
                    <p class="text-carbon-300 leading-relaxed text-sm">{{ settings['about.mission'] }}</p>
                </div>
                <div v-if="settings?.['about.vision']" class="glass-card-hover p-8 reveal" style="transition-delay:100ms">
                    <div class="w-12 h-12 rounded-xl bg-industrial-500/10 flex items-center justify-center mb-5">
                        <svg class="w-6 h-6 text-industrial-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </div>
                    <h3 class="text-lg font-display font-bold text-white mb-3">Tầm nhìn</h3>
                    <p class="text-carbon-300 leading-relaxed text-sm">{{ settings['about.vision'] }}</p>
                </div>
            </section>

            <!-- Contact -->
            <section class="glass-card p-8 text-center reveal relative overflow-hidden">
                <div class="absolute inset-0 bg-grid opacity-10 pointer-events-none"></div>
                <div class="absolute top-0 right-0 w-64 h-64 bg-volt-500/[0.04] rounded-full blur-[80px] pointer-events-none"></div>
                <div class="relative">
                    <h3 class="text-xl font-display font-bold text-white mb-6">Liên hệ với chúng tôi</h3>
                    <div class="flex flex-wrap justify-center gap-8 text-sm">
                        <p v-if="settings?.['site.phone']" class="flex items-center gap-2 text-carbon-300">
                            <svg class="w-4 h-4 text-volt-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            {{ settings['site.phone'] }}
                        </p>
                        <p v-if="settings?.['site.email']" class="flex items-center gap-2 text-carbon-300">
                            <svg class="w-4 h-4 text-volt-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            {{ settings['site.email'] }}
                        </p>
                        <p v-if="settings?.['site.address']" class="flex items-center gap-2 text-carbon-300">
                            <svg class="w-4 h-4 text-volt-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            {{ settings['site.address'] }}
                        </p>
                    </div>
                </div>
            </section>
        </div>
    </GuestPageLayout>
</template>
