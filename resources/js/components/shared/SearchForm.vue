<script setup lang="ts">
import { Input } from '@/components/ui/input';
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Search } from 'lucide-vue-next';

const props = defineProps<{
    url: string;
    class?: string;
}>();

const params = new URLSearchParams(window.location.search);

const search = ref(params.get('search') || '');

let delayTimeout: ReturnType<typeof setTimeout>;

const handleSearch = (payload: string|number) => {
    clearTimeout(delayTimeout);

    delayTimeout = setTimeout(() => {
        router.get(props.url, {
            search: payload
        },{
            preserveScroll: true,
        });
    }, 500);
}

</script>
<template>
    <div class="relative">
        <Input :class="props.class" type="text" v-model="search" placeholder="Search" v-on:update:model-value="handleSearch" />
        <Search class="absolute size-4 right-2 top-1/2 -translate-y-1/2 text-gray-500"
         />
    </div>
</template>
