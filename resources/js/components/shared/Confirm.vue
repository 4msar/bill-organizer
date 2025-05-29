<script setup lang="ts">
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { cn } from '@/lib/utils';
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Button, buttonVariants } from '../ui/button';

withDefaults(
    defineProps<{
        url: string;
        title?: string;
        modal?: boolean;
        description?: string;
    }>(),
    {
        title: 'Are you sure?',
        modal: false,
    },
);

defineEmits(['confirm', 'cancel']);

const open = ref(false);
</script>

<template>
    <Dialog v-if="modal" v-model:open="open">
        <DialogTrigger as-child>
            <slot />
        </DialogTrigger>
        <DialogContent class="sm:max-w-md">
            <DialogHeader class="space-y-3">
                <DialogTitle>{{ title }}</DialogTitle>
                <DialogDescription v-if="description">
                    {{ description }}
                </DialogDescription>
            </DialogHeader>

            <DialogFooter class="gap-2">
                <DialogClose as-child>
                    <Button variant="secondary" @click="open = false"> Cancel </Button>
                </DialogClose>

                <Link :class="cn(buttonVariants({ variant: 'destructive' }))" :href="url" method="delete" as="button" class="flex items-center gap-1"
                    >Delete</Link
                >
            </DialogFooter>
        </DialogContent>
    </Dialog>

    <Popover v-else v-model:open="open" trigger="click">
        <PopoverTrigger as-child>
            <slot />
        </PopoverTrigger>
        <PopoverContent arrow class="w-fit min-w-30 p-0">
            <p class="mb-2 px-2 pt-2">{{ title }}</p>
            <hr class="my-2 w-full border-t border-slate-200 dark:border-slate-700" />
            <div class="flex items-center justify-between space-x-2 px-2 pb-2">
                <Button class="px-2 py-0" variant="secondary" size="xs" @click="open = false">No</Button>
                <Link
                    :class="cn(buttonVariants({ variant: 'destructive', size: 'xs' }))"
                    :href="url"
                    method="delete"
                    as="button"
                    class="flex items-center gap-1"
                    >Yes</Link
                >
            </div>
        </PopoverContent>
    </Popover>
</template>
