<script setup lang="ts">
import { LucideCurrency } from 'lucide-vue-next';
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
    <LucideCurrency :class="className" v-bind="$attrs" />
</template>
