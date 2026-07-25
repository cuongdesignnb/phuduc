<script setup>
defineProps({ errors: { type: Object, default: () => ({}) } });
</script>

<template>
    <div v-if="Object.keys(errors).length" class="border border-admin-danger bg-admin-danger/10 p-4 text-sm text-admin-danger" role="alert" aria-live="assertive">
        <p class="font-semibold">Vui lòng kiểm tra lại thông tin.</p>
        <ul class="mt-2 list-disc pl-5"><li v-for="(error, key) in errors" :key="key">{{ Array.isArray(error) ? error[0] : error }}</li></ul>
    </div>
</template>
