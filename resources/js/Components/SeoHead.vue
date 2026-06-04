<script setup>
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    title: String,
    description: String,
    ogImage: String,
    ogType: { type: String, default: 'website' },
    canonical: String,
    robots: { type: String, default: 'index, follow' },
    jsonLd: { type: [Object, Array], default: null },
});

const jsonLdScript = computed(() => {
    if (!props.jsonLd) return null;
    const data = Array.isArray(props.jsonLd) ? props.jsonLd : [props.jsonLd];
    return data.filter(Boolean);
});
</script>

<template>
    <Head :title="title">
        <meta v-if="description" name="description" :content="description" />
        <meta v-if="robots" name="robots" :content="robots" />

        <!-- Open Graph -->
        <meta v-if="title" property="og:title" :content="title" />
        <meta v-if="description" property="og:description" :content="description" />
        <meta v-if="ogImage" property="og:image" :content="ogImage" />
        <meta v-if="ogType" property="og:type" :content="ogType" />
        <meta v-if="canonical" property="og:url" :content="canonical" />

        <!-- Twitter Card -->
        <meta name="twitter:card" content="summary_large_image" />
        <meta v-if="title" name="twitter:title" :content="title" />
        <meta v-if="description" name="twitter:description" :content="description" />
        <meta v-if="ogImage" name="twitter:image" :content="ogImage" />

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
