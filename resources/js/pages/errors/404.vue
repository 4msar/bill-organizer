<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';

const page = usePage();
console.log(JSON.parse(JSON.stringify(page))
);
</script>

<template>

    <Head title="404 - Page Not Found" />
    <div class="min-h-screen flex items-center justify-center bg-gray-100 dark:bg-gray-900">
        <div class="text-center">
            <h1 class="text-6xl font-bold text-gray-800 dark:text-gray-200 mb-4">404</h1>
            <p class="text-xl text-gray-600 dark:text-gray-400 mb-8">Oops! Page not found.</p>

            <Link v-if="$page?.props?.auth?.user" :href="route('dashboard')">
            Back to dashboard
            </Link>
            <a v-else href="/"
                class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-gray-300 focus:bg-gray-700 dark:focus:bg-gray-300 active:bg-gray-900 dark:active:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Back to Home
            </a>
        </div>
    </div>
</template>