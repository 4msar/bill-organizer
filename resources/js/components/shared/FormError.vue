<script setup lang="ts">
defineProps<{
    message?: string;
}>();
</script>
<template>
    <span v-if="message" class="text-xs text-red-500 italic">{{ message }}</span>
</template>
