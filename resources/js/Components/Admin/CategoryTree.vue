<script setup>
import { Link } from '@inertiajs/vue3';
defineProps({ items: { type: Array, default: () => [] } });
</script>

<template>
    <ul class="space-y-2"><li v-for="category in items" :key="category.id" class="border border-admin-border p-3"><div class="flex items-center justify-between gap-3"><div><Link :href="category.edit_url" class="font-medium text-admin-content hover:text-admin-accent">{{ category.name }}</Link><p class="text-xs text-admin-content-muted">{{ category.posts_count }} bài viết · {{ category.children_count }} danh mục con</p></div><div><Link :href="category.edit_url" class="mr-3 text-sm text-admin-accent">Sửa</Link><button v-if="category.can_delete" type="button" class="text-sm text-admin-danger" @click="$emit('remove', category)">Xóa</button></div></div><CategoryTree v-if="category.children?.length" class="mt-3 border-l border-admin-border pl-4" :items="category.children" @remove="$emit('remove', $event)" /></li></ul>
</template>

<script>
export default { name: 'CategoryTree' };
</script>
