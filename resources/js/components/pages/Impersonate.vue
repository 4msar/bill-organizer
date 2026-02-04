<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        v-if="$page.props.auth.user.is_impersonating"
        class="fixed right-4 bottom-4 z-20 rounded-md border border-yellow-400 bg-yellow-100 px-4 py-3 text-yellow-700 shadow-md"
        role="alert"
    >
        <strong class="font-bold">You are impersonating another user.</strong>
        <span class="block sm:inline">
            Click <Link :preserve-state="false" :href="route('impersonate.leave')" class="underline">here</Link> to stop impersonating.
        </span>
    </div>
</template>
