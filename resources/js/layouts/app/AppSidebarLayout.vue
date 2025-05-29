<script setup lang="ts">
import AppContent from '@/components/shared/AppContent.vue';
import AppShell from '@/components/shared/AppShell.vue';
import AppSidebar from '@/components/shared/AppSidebar.vue';
import AppSidebarHeader from '@/components/shared/AppSidebarHeader.vue';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar />
        <AppContent variant="sidebar">
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
            <slot />
        </AppContent>
    </AppShell>
</template>
