<script setup lang="ts">
import ErrorContent from '@/components/pages/ErrorContent.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage<{ status: string; message: string }>();
console.log(JSON.parse(JSON.stringify(page)));

const title = computed(() => {
    const titleBasedOnStatus: Record<number, string> = {
        403: '403 - Forbidden',
        404: '404 - Page Not Found',
        500: '500 - Internal Server Error',
        503: '503 - Service Unavailable',
    };
    if (page.props.status && titleBasedOnStatus[Number(page.props.status)]) {
        return titleBasedOnStatus[Number(page.props.status)];
    }

    return 'Error';
});
const message = computed(() => {
    if (page.props.message) {
        return page.props.message;
    }

    return 'An unexpected error has occurred.';
});
</script>

<template>
    <Head :title="title" />
    <AppLayout v-if="$page?.props?.auth?.user">
        <ErrorContent :title="title" :message="message" />
    </AppLayout>
    <ErrorContent v-else :title="title" :message="message" />
</template>
