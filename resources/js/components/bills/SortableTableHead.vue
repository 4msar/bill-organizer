<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { TableHead } from '@/components/ui/table';
import { router } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    field: string;
    label: string;
    routeName?: string;
    default?: boolean;
}>();

function handleSort() {
    const routeName = props.routeName || 'bills.index';
    const direction = route().params.sort_direction || 'asc';

    router.get(
        route(routeName),
        {
            ...route().params,
            sort_by: props.field,
            sort_direction: direction === 'asc' ? 'desc' : 'asc',
        },
        { preserveState: false },
    );
}

const sortBy = computed(() => route().params.sort_by || (props.default ? props.field : undefined));
const sortDirection = computed(() => route().params.sort_direction || 'asc');
</script>

<template>
    <TableHead>
        <Button variant="ghost" @click="handleSort" class="h-auto p-0 font-semibold hover:bg-transparent">
            {{ label }}
            <span v-if="sortBy === field" class="ml-1">
                {{ sortDirection === 'asc' ? '↑' : '↓' }}
            </span>
        </Button>
    </TableHead>
</template>
