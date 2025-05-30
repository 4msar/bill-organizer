<script setup lang="ts">
import AlertNotification from '@/components/shared/AlertNotification.vue';
import AuthLayout from '@/layouts/auth/AuthSimpleLayout.vue';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <AuthLayout :title="title" :description="description">
        <slot />
        <AlertNotification />
    </AuthLayout>
</template>
