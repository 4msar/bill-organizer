<script setup lang="ts">
import { SharedData } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { capitalize, computed, onMounted, watch } from 'vue';
import { toast as notify } from 'vue-sonner';
import { Toaster } from '../ui/sonner';

const page = usePage<SharedData>();

const flash = computed(() => page.props.flash);

type FlashType = 'success' | 'info' | 'warning' | 'error';

onMounted(() => {
    if (Object.values(flash.value).filter(Boolean).length > 0) {
        Object.entries(flash.value).forEach(([key, value]) => {
            if (value) {
                const toast = notify[key as FlashType] ?? notify;

                toast(capitalize(key), {
                    description: value,
                    duration: 5000,
                    closeButton: true,
                });
            }
        });
    }
});

watch(
    () => flash,
    () => {
        if (Object.values(flash.value).filter(Boolean).length > 0) {
            Object.entries(flash.value).forEach(([key, value]) => {
                if (value) {
                    const toast = notify[key as FlashType] ?? notify;

                    toast(capitalize(key), {
                        description: value,
                        duration: 5000,
                        closeButton: true,
                    });
                }
            });
        }
    },
    { deep: true },
);
</script>

<template>
    <Toaster />
</template>
