<script setup lang="ts" generic="T">
import { cn } from '@/lib/utils';
import { PaginationData } from '@/types';
import { Link } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { buttonVariants } from '../ui/button';

const { data: pagination } = defineProps<{ data: PaginationData<T[]> }>();
</script>
<template>
    <nav
        v-if="pagination.links.length > 0 && (pagination.next_page_url || pagination.prev_page_url)"
        class="flex-column flex flex-wrap items-center justify-between gap-2 py-5 pt-6 sm:flex-row"
        aria-label="Table navigation"
    >
        <div class="mb-4 inline-flex w-full items-center gap-1.5 text-sm font-normal text-gray-500 md:mb-0 md:w-auto dark:text-gray-400">
            Showing
            <span class="font-semibold text-gray-900 dark:text-white"> {{ pagination.from }} - {{ pagination.to }} </span>
            of
            <span class="font-semibold text-gray-900 dark:text-white">{{ pagination.total }}</span>
        </div>

        <ul class="inline-flex w-full flex-wrap justify-end gap-1 -space-x-px text-sm md:w-auto md:flex-nowrap rtl:space-x-reverse">
            <template v-for="link in pagination.links" :key="link.label">
                <li>
                    <Link
                        preserve-scroll
                        :href="link.url ?? ''"
                        :disabled="link.url === null || link.active"
                        :class="
                            cn(
                                buttonVariants({
                                    variant: link?.active ? 'outline' : 'ghost',
                                    size: 'sm',
                                }),
                                link.active || link.url === null ? 'text-muted-foreground pointer-events-none' : '',
                            )
                        "
                    >
                        <ChevronLeft v-if="link.label.indexOf('Previous') !== -1" class="rtl:rotate-180" />
                        <span class="flex items-center justify-center">
                            {{ link.label }}
                        </span>
                        <ChevronRight v-if="link.label.indexOf('Next') !== -1" class="rtl:rotate-180" />
                    </Link>
                </li>
            </template>
        </ul>
    </nav>
</template>
