<script setup lang="ts">
import AppLogoIcon from '@/components/shared/AppLogoIcon.vue';
</script>

<template>
    <div
        class="bg-sidebar-primary dark:bg-transparent dark:border dark:border-white text-sidebar-primary-foreground flex aspect-square size-8 items-center justify-center rounded-md">
        <AppLogoIcon class="size-5" />
    </div>
    <div class="ml-1 grid flex-1 text-left text-sm">
        <span class="mb-0.5 truncate leading-none font-semibold">{{ $page.props.name }}</span>
    </div>
</template>
