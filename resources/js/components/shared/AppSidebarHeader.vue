<script setup lang="ts">
import Breadcrumbs from '@/components/shared/Breadcrumbs.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItemType } from '@/types';
import Tooltip from './Tooltip.vue';

defineProps<{
    breadcrumbs?: BreadcrumbItemType[];
}>();
</script>

<template>
    <header
        class="border-sidebar-border/70 bg-background dark:bg-background sticky top-0 z-20 flex h-16 shrink-0 items-center justify-between gap-2 border-b px-6 shadow-sm transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:relative md:px-4 md:shadow-none print:hidden"
    >
        <div class="flex items-center gap-2">
            <SidebarTrigger class="-ml-1" />
            <template v-if="breadcrumbs && breadcrumbs.length > 0">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </template>
        </div>

        <div>
            <Tooltip title="Current app version">
                <span class="text-muted-foreground border-muted-foreground rounded border px-1 text-xs">{{ $page.props.version }}</span>
            </Tooltip>
        </div>
    </header>
</template>
