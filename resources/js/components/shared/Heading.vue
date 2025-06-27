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
    <header :class="cn('mb-8 flex items-center gap-2', props.class)">
        <div class="space-y-0.5">
            <h2 class="text-xl font-semibold tracking-tight">{{ title }}</h2>
            <p v-if="description" class="text-muted-foreground text-sm">
                {{ description }}
            </p>
        </div>
        <slot />
    </header>
</template>
