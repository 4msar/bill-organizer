<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';

const page = usePage();
console.log(JSON.parse(JSON.stringify(page)));
</script>

<template>
    <Head title="404 - Page Not Found" />
    <div class="flex min-h-screen items-center justify-center bg-gray-100 dark:bg-gray-900">
        <div class="text-center">
            <h1 class="mb-4 text-6xl font-bold text-gray-800 dark:text-gray-200">404</h1>
            <p class="mb-8 text-xl text-gray-600 dark:text-gray-400">Oops! Page not found.</p>

            <Link v-if="$page?.props?.auth?.user" :href="route('dashboard')"> Back to dashboard </Link>
            <a
                v-else
                href="/"
                class="inline-flex items-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out hover:bg-gray-700 focus:bg-gray-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none active:bg-gray-900 dark:bg-gray-200 dark:text-gray-800 dark:hover:bg-gray-300 dark:focus:bg-gray-300 dark:active:bg-gray-400"
            >
                Back to Home
            </a>
        </div>
    </div>
</template>
