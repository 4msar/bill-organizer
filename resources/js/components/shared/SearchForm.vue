<script setup lang="ts">
import { Input } from '@/components/ui/input';
import { router } from '@inertiajs/vue3';
import { Search } from 'lucide-vue-next';
import { ref, watch } from 'vue';

const props = defineProps<{
    url: string;
    class?: string;
}>();

const params = new URLSearchParams(window.location.search);
const queryParams = ref<Record<string, string | number>>({});

watch(
    () => window.location.search,
    (newSearch) => {
        const newParams = new URLSearchParams(newSearch);
        const updatedParams: Record<string, string> = {};
        newParams.forEach((value, key) => {
            updatedParams[key] = value;
        });
        queryParams.value = updatedParams;
    },
    { immediate: true },
);

const search = ref(params.get('search') || '');

let delayTimeout: ReturnType<typeof setTimeout>;

const handleSearch = (searchStr: string | number) => {
    searchHandler({ search: searchStr });
};

const searchHandler = (payload: Record<string, string | number>) => {
    const data = { ...queryParams.value, ...payload };
    // remove empty values
    Object.keys(data).forEach((key) => {
        if (data[key] === '' || data[key] === null) {
            delete data[key];
        }

        // add to queryParams ref
        queryParams.value = { ...data };
    });

    processSearch(data);
};

const processSearch = (data: Record<string, string | number>) => {
    clearTimeout(delayTimeout);
    delayTimeout = setTimeout(() => {
        router.get(props.url, data, {
            preserveScroll: true,
            preserveState: false,
        });
    }, 1000);
};
</script>
<template>
    <div :class="props.class">
        <div class="relative w-full sm:w-fit">
            <Input
                ref="input"
                autofocus
                class="w-full sm:w-fit"
                type="text"
                v-model="search"
                placeholder="Search"
                v-on:update:model-value="handleSearch"
            />
            <Search class="absolute top-1/2 right-2 size-4 -translate-y-1/2 text-gray-500" />
        </div>
        <slot :searchHandler="searchHandler" :queryParams="queryParams" />
    </div>
</template>
