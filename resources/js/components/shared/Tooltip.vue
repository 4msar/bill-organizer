<script setup lang="ts">
import { Tooltip, TooltipContent, TooltipTrigger } from '../ui/tooltip';

defineProps<{
    title: string | (() => string) | (() => any);
}>();
</script>

<template>
    <Tooltip>
        <TooltipTrigger as-child>
            <slot />
        </TooltipTrigger>
        <TooltipContent>
            <template v-if="typeof title === 'string'">
                {{ title }}
            </template>
            <component :is="title" v-else />
        </TooltipContent>
    </Tooltip>
</template>
