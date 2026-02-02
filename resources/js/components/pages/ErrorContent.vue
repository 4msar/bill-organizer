<script lang="ts" setup>
import { Link } from '@inertiajs/vue3';
import { Button } from '../ui/button';

withDefaults(
    defineProps<{
        title?: string;
        message?: string;
    }>(),
    {
        title: '404 - Page Not Found',
        message: 'Oops! Page not found.',
    },
);
</script>

<template>
    <div class="flex min-h-screen items-center justify-center bg-gray-100 dark:bg-gray-900">
        <div class="text-center">
            <h1 class="mb-4 text-6xl font-bold text-gray-800 dark:text-gray-200">{{ title }}</h1>
            <p class="mb-8 text-xl text-gray-600 dark:text-gray-400">{{ message }}</p>

            <Button v-if="$page?.props?.auth?.user" variant="destructive" asChild>
                <Link :href="route('dashboard')"> Back to dashboard </Link>
            </Button>
            <Button v-else variant="destructive" asChild>
                <a href="/"> Back to Home </a>
            </Button>
        </div>
    </div>
</template>
