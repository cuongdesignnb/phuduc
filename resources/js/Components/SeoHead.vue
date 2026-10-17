<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import { computed, onMounted } from 'vue';

const props = defineProps({
    title: String,
    description: String,
    ogTitle: String,
    ogDescription: String,
    ogImage: String,
    ogImageAlt: String,
    ogType: { type: String, default: 'website' },
    ogUrl: String,
    siteName: String,
    locale: { type: String, default: 'vi_VN' },
    canonical: String,
    robots: { type: String, default: 'index, follow' },
    twitterCard: { type: String, default: 'summary_large_image' },
    twitterTitle: String,
    twitterDescription: String,
    twitterImage: String,
    twitterImageAlt: String,
    publishedTime: String,
    modifiedTime: String,
    section: String,
    jsonLd: { type: [Object, Array], default: null },
});

const page = usePage();
const robotsContent = computed(() => page.props.site?.prevent_indexing ? 'noindex, nofollow' : props.robots);

const jsonLdScript = computed(() => {
    if (!props.jsonLd) return null;
    const data = Array.isArray(props.jsonLd) ? props.jsonLd : [props.jsonLd];
    return data.filter(Boolean);
});

onMounted(() => {
    document.querySelectorAll('[data-server-seo]').forEach((element) => element.remove());
});
</script>

<template>
    <Head :title="title">
        <meta v-if="description" name="description" :content="description" />
        <meta v-if="robotsContent" name="robots" :content="robotsContent" />

        <!-- Open Graph -->
        <meta v-if="ogTitle || title" property="og:title" :content="ogTitle || title" />
        <meta v-if="ogDescription || description" property="og:description" :content="ogDescription || description" />
        <meta v-if="ogImage" property="og:image" :content="ogImage" />
        <meta v-if="ogImageAlt" property="og:image:alt" :content="ogImageAlt" />
        <meta v-if="ogType" property="og:type" :content="ogType" />
        <meta v-if="ogUrl || canonical" property="og:url" :content="ogUrl || canonical" />
        <meta v-if="siteName" property="og:site_name" :content="siteName" />
        <meta v-if="locale" property="og:locale" :content="locale" />
        <meta v-if="publishedTime && ogType === 'article'" property="article:published_time" :content="publishedTime" />
        <meta v-if="modifiedTime && ogType === 'article'" property="article:modified_time" :content="modifiedTime" />
        <meta v-if="section && ogType === 'article'" property="article:section" :content="section" />

        <!-- Twitter Card -->
        <meta name="twitter:card" :content="twitterCard" />
        <meta v-if="twitterTitle || title" name="twitter:title" :content="twitterTitle || title" />
        <meta v-if="twitterDescription || description" name="twitter:description" :content="twitterDescription || description" />
        <meta v-if="twitterImage || ogImage" name="twitter:image" :content="twitterImage || ogImage" />
        <meta v-if="twitterImageAlt || ogImageAlt" name="twitter:image:alt" :content="twitterImageAlt || ogImageAlt" />

        <!-- Canonical -->
        <link v-if="canonical" rel="canonical" :href="canonical" />

        <!-- JSON-LD Structured Data -->
        <component
            v-for="(schema, idx) in jsonLdScript"
            :key="idx"
            :is="'script'"
            type="application/ld+json"
            v-text="JSON.stringify(schema)"
        />
    </Head>
</template>
