<script setup lang="ts">
import { useAppearance } from '@/composables/useAppearance';
import { Monitor, Moon, Sun } from 'lucide-vue-next';
import { computed } from 'vue';

interface Props {
    class?: string;
}

const { class: containerClass = '' } = defineProps<Props>();

const { appearance, updateAppearance } = useAppearance();

const themeSequence = ['light', 'dark', 'system'] as const;

const currentIcon = computed(() => {
    switch (appearance.value) {
        case 'light':
            return Sun;
        case 'dark':
            return Moon;
        case 'system':
            return Monitor;
        default:
            return Sun;
    }
});

const currentLabel = computed(() => {
    switch (appearance.value) {
        case 'light':
            return 'Light';
        case 'dark':
            return 'Dark';
        case 'system':
            return 'System';
        default:
            return 'Light';
    }
});

const cycleTheme = () => {
    const currentIndex = themeSequence.indexOf(appearance.value);
    const nextIndex = (currentIndex + 1) % themeSequence.length;
    updateAppearance(themeSequence[nextIndex]);
};
</script>

<template>
    <button
        @click="cycleTheme"
        :class="['flex items-center rounded-lg px-3 py-2 transition-colors hover:bg-neutral-100 dark:hover:bg-neutral-800', containerClass]"
        :title="`Current theme: ${currentLabel}`"
    >
        <component :is="currentIcon" class="h-5 w-5 text-neutral-700 dark:text-neutral-300" />
    </button>
</template>
