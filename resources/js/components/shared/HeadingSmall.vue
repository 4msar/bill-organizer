<script setup lang="ts">
import { cn } from '@/lib/utils';

interface Props {
    title: string;
    description?: string;
    class?: string;
}

const props = defineProps<Props>();
</script>

<template>
    <header :class="cn('flex items-center gap-2', props.class)">
        <div>
            <h3 class="mb-0.5 text-base font-medium">{{ title }}</h3>
            <p v-if="description" class="text-muted-foreground text-sm">
                {{ description }}
            </p>
        </div>
        <slot />
    </header>
</template>
