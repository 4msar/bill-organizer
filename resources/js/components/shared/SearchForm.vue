<script setup lang="ts">
import { Input } from '@/components/ui/input';
import { router } from '@inertiajs/vue3';
import { Search } from 'lucide-vue-next';
import { ref, useTemplateRef } from 'vue';

const props = defineProps<{
    url: string;
    class?: string;
}>();

const params = new URLSearchParams(window.location.search);

const input = useTemplateRef<HTMLInputElement>('input');
const search = ref(params.get('search') || '');

let delayTimeout: ReturnType<typeof setTimeout>;

const handleSearch = (payload: string | number) => {
    clearTimeout(delayTimeout);

    delayTimeout = setTimeout(() => {
        router.get(
            props.url,
            {
                search: payload,
            },
            {
                preserveScroll: true,
                preserveState: false,
                onFinish: () => {
                    input.value?.focus();
                    console.log('onFinish', input);
                },
            },
        );
    }, 500);
};
</script>
<template>
    <div class="relative">
        <Input ref="input" autofocus :class="props.class" type="text" v-model="search" placeholder="Search" v-on:update:model-value="handleSearch" />
        <Search class="absolute top-1/2 right-2 size-4 -translate-y-1/2 text-gray-500" />
    </div>
</template>
