<script setup lang="ts" generic="T">
import { PaginationData } from '@/types';
import { Link } from '@inertiajs/vue3';

const { data: pagination } = defineProps<{ data: PaginationData<T[]> }>();
</script>
<template>
    <nav
        v-if="pagination.links.length > 0 && (pagination.next_page_url || pagination.prev_page_url)"
        class="flex-column flex flex-wrap items-center justify-between py-5 pt-6 md:flex-row"
        aria-label="Table navigation"
    >
        <span class="mb-4 block w-full text-sm font-normal text-gray-500 md:mb-0 md:inline md:w-auto dark:text-gray-400"
            >Showing
            <span class="font-semibold text-gray-900 dark:text-white"> {{ pagination.from }} - {{ pagination.to }} </span>
            of
            <span class="font-semibold text-gray-900 dark:text-white">{{ pagination.total }}</span></span
        >

        <ul class="inline-flex h-8 -space-x-px text-sm rtl:space-x-reverse">
            <template v-for="(link, index) in pagination.links" :key="link.label">
                <li>
                    <Link
                        preserve-scroll
                        :href="link.url ?? ''"
                        :disabled="link.url === null || link.active"
                        class="flex h-8 items-center justify-center border border-gray-300 px-3"
                        :class="{
                            'cursor-not-allowed border bg-blue-50 text-blue-600 hover:bg-blue-100 hover:text-blue-700 dark:border-gray-700 dark:bg-gray-700 dark:text-white':
                                link.active,
                            'bg-white leading-tight text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white':
                                !link.active,
                            '!text-gray-500': !link.url,
                            'rounded-s-lg': index === 0,
                            'rounded-e-lg': pagination.links.length === index + 1,
                        }"
                    >
                        <span v-html="link.label"></span>
                    </Link>
                </li>
            </template>
        </ul>
    </nav>
</template>
