<script setup>
import AdminShell from '@/Components/Admin/AdminShell.vue';
</script>

<template>
    <AdminShell>
        <template v-if="$slots.header" #header><slot name="header" /></template>
        <template v-if="$slots.breadcrumb" #breadcrumb><slot name="breadcrumb" /></template>
        <slot />
    </AdminShell>
</template>
