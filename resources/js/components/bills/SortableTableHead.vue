<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { TableHead } from '@/components/ui/table';
import { router } from '@inertiajs/vue3';

const props = defineProps<{
    field: string;
    label: string;
    routeName?: string;
}>();

function handleSort() {
    const routeName = props.routeName || 'bills.index';
    router.get(
        route(routeName),
        {
            ...route().params,
            sort_by: props.field,
            sort_direction: route().params.sort_by === props.field && route().params.sort_direction === 'asc' ? 'desc' : 'asc',
        },
        { preserveState: false },
    );
}
</script>

<template>
    <TableHead>
        <Button variant="ghost" @click="handleSort" class="h-auto p-0 font-semibold hover:bg-transparent">
            {{ label }}
            <span v-if="route().params.sort_by === field" class="ml-1">
                {{ route().params.sort_direction === 'asc' ? '↑' : '↓' }}
            </span>
        </Button>
    </TableHead>
</template>
