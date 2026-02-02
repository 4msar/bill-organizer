<script setup lang="ts">
import ErrorContent from '@/components/pages/ErrorContent.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';

const page = usePage();
console.log(JSON.parse(JSON.stringify(page)));
</script>

<template>
    <Head title="404 - Page Not Found" />
    <AppLayout v-if="$page?.props?.auth?.user">
        <ErrorContent title="404 - Not found" message="Oops! Page not found." />
    </AppLayout>
    <ErrorContent v-else title="404 - Not found" message="Oops! Page not found." />
</template>
