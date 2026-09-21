<script setup>
defineProps({
    html: { type: String, default: '' },
});
</script>

<template>
    <div
        v-if="html"
        class="rich-content"
        v-html="html"
    />
</template>

<style scoped>
.rich-content {
    color: rgb(var(--ds-content-secondary));
    font-size: 1rem;
    line-height: 1.8;
}

.rich-content :deep(p) {
    margin: 1rem 0;
}

.rich-content :deep(h2),
.rich-content :deep(h3),
.rich-content :deep(h4) {
    color: rgb(var(--ds-content-primary));
    font-family: var(--font-display), sans-serif;
    font-weight: 700;
    line-height: 1.25;
}

.rich-content :deep(h2) {
    margin: 2.5rem 0 1rem;
    padding-bottom: 0.7rem;
    border-bottom: 1px solid rgb(var(--ds-border-default));
    font-size: clamp(1.35rem, 2vw, 1.65rem);
}

.rich-content :deep(h3) {
    margin: 2rem 0 0.75rem;
    font-size: 1.2rem;
}

.rich-content :deep(h4) {
    margin: 1.5rem 0 0.65rem;
    font-size: 1.05rem;
}

.rich-content :deep(ul),
.rich-content :deep(ol) {
    margin: 1rem 0;
    padding-left: 1.35rem;
}

.rich-content :deep(ul) {
    list-style: disc;
}

.rich-content :deep(ol) {
    list-style: decimal;
}

.rich-content :deep(ol li[data-list='bullet']) {
    list-style: disc;
}

.rich-content :deep(li) {
    margin: 0.45rem 0;
    padding-left: 0.25rem;
}

.rich-content :deep(li::marker) {
    color: rgb(var(--ds-brand-text));
}

.rich-content :deep(a) {
    color: rgb(var(--ds-brand-text));
    font-weight: 600;
    text-decoration: underline;
    text-decoration-color: rgb(var(--ds-brand-border));
    text-underline-offset: 0.2em;
}

.rich-content :deep(a:hover) {
    color: rgb(var(--ds-brand-hover));
    text-decoration-color: currentColor;
}

.rich-content :deep(strong) {
    color: rgb(var(--ds-content-primary));
    font-weight: 700;
}

.rich-content :deep(blockquote) {
    margin: 1.5rem 0;
    border-left: 3px solid rgb(var(--ds-brand-primary));
    background: rgb(var(--ds-brand-soft));
    padding: 1rem 1.25rem;
    color: rgb(var(--ds-content-primary));
    font-style: italic;
}

.rich-content :deep(pre) {
    margin: 1.5rem 0;
    overflow-x: auto;
    border-radius: 0.75rem;
    background: rgb(var(--ds-surface-inverse));
    padding: 1rem 1.25rem;
    color: rgb(var(--ds-content-inverse));
    font-size: 0.875rem;
    line-height: 1.65;
}

.rich-content :deep(code) {
    border-radius: 0.25rem;
    background: rgb(var(--ds-surface-muted));
    padding: 0.1rem 0.3rem;
    font-size: 0.9em;
}

.rich-content :deep(pre code) {
    border-radius: 0;
    background: transparent;
    padding: 0;
    color: inherit;
}

.rich-content :deep(figure) {
    margin: 1.75rem 0;
}

.rich-content :deep(img) {
    display: block;
    max-width: 100%;
    height: auto;
    margin: 1.5rem auto;
    border-radius: 0.875rem;
}

.rich-content :deep(figcaption) {
    margin-top: -0.75rem;
    text-align: center;
    font-size: 0.8125rem;
    color: rgb(var(--ds-content-muted));
}

.rich-content :deep(table) {
    display: block;
    width: 100%;
    overflow-x: auto;
    margin: 1.5rem 0;
    border-collapse: collapse;
    border: 1px solid rgb(var(--ds-border-default));
}

.rich-content :deep(th),
.rich-content :deep(td) {
    min-width: 9rem;
    border: 1px solid rgb(var(--ds-border-default));
    padding: 0.75rem 1rem;
    text-align: left;
}

.rich-content :deep(th) {
    background: rgb(var(--ds-surface-muted));
    color: rgb(var(--ds-content-primary));
    font-weight: 700;
}

.rich-content :deep(hr) {
    margin: 2rem 0;
    border: 0;
    border-top: 1px solid rgb(var(--ds-border-default));
}

.rich-content :deep(.ql-align-center) { text-align: center; }
.rich-content :deep(.ql-align-right) { text-align: right; }
.rich-content :deep(.ql-align-justify) { text-align: justify; }
.rich-content :deep(.ql-indent-1) { margin-left: 3rem; }
.rich-content :deep(.ql-indent-2) { margin-left: 6rem; }
.rich-content :deep(.ql-indent-3) { margin-left: 9rem; }
.rich-content :deep(.ql-ui) { display: none; }

@media (max-width: 639px) {
    .rich-content {
        font-size: 0.9375rem;
    }

    .rich-content :deep(h2) {
        margin-top: 2rem;
    }

    .rich-content :deep(th),
    .rich-content :deep(td) {
        min-width: 7.5rem;
        padding: 0.625rem 0.75rem;
    }
}
</style>
