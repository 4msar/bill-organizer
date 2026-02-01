<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuSub,
    DropdownMenuSubContent,
    DropdownMenuSubTrigger,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { ExportFormat, useNoteExport } from '@/composables/useNoteExport';
import { formatDate, getLink, simpleMarkdown } from '@/lib/utils';
import { Note } from '@/types/model';
import { Link } from '@inertiajs/vue3';
import { Download, MoreVertical, Pencil, Pin, StickyNote, Trash } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    note?: Note | null;
}>();

const emit = defineEmits<{
    edit: [note: Note];
    delete: [note: Note];
    pin: [note: Note];
}>();

const { exportNote } = useNoteExport();

const handleEdit = () => {
    if (props.note) {
        emit('edit', props.note);
    }
};

const handleDelete = () => {
    if (props.note) {
        emit('delete', props.note);
    }
};

const handlePin = () => {
    if (props.note) {
        emit('pin', props.note);
    }
};

const handleExport = (format: ExportFormat) => {
    if (props.note) {
        exportNote(props.note, format);
    }
};

const formattedContent = computed(() => {
    if (!props.note?.content) return '';

    // Simple markdown-like formatting
    return simpleMarkdown(props.note.content);
});
</script>

<template>
    <div v-if="note" class="flex h-full flex-col">
        <Card class="flex h-full flex-col border-0 shadow-none">
            <CardHeader class="border-b">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0 flex-1">
                        <CardTitle class="text-2xl break-words">
                            {{ note.title || 'Untitled Note' }}
                        </CardTitle>
                        <CardDescription class="mt-2 flex flex-wrap items-center gap-2">
                            <span>Updated {{ formatDate(note.updated_at || note.created_at, true) }}</span>
                            <Pin v-if="note.is_pinned" class="text-muted-foreground h-3 w-3" />
                        </CardDescription>
                    </div>
                    <div class="flex flex-shrink-0 items-center gap-2">
                        <Button variant="outline" size="sm" @click="handleEdit" class="hidden sm:flex">
                            <Pencil class="mr-2 h-4 w-4" />
                            Edit
                        </Button>
                        <Button variant="outline" size="icon" @click="handleEdit" class="sm:hidden">
                            <Pencil class="h-4 w-4" />
                        </Button>
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button variant="ghost" size="icon" class="h-8 w-8">
                                    <MoreVertical class="h-4 w-4" />
                                    <span class="sr-only">Open menu</span>
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                                <DropdownMenuItem @click="handlePin">
                                    <Pin class="mr-2 h-4 w-4" />
                                    {{ note.is_pinned ? 'Unpin' : 'Pin' }}
                                </DropdownMenuItem>
                                <DropdownMenuSub>
                                    <DropdownMenuSubTrigger>
                                        <Download class="mr-2 h-4 w-4" />
                                        Export
                                    </DropdownMenuSubTrigger>
                                    <DropdownMenuSubContent>
                                        <DropdownMenuItem @click="handleExport('json')"> Export as JSON </DropdownMenuItem>
                                        <DropdownMenuItem @click="handleExport('markdown')"> Export as Markdown </DropdownMenuItem>
                                        <DropdownMenuItem @click="handleExport('csv')"> Export as CSV </DropdownMenuItem>
                                        <DropdownMenuItem @click="handleExport('txt')"> Export as Text </DropdownMenuItem>
                                    </DropdownMenuSubContent>
                                </DropdownMenuSub>
                                <DropdownMenuSeparator />
                                <DropdownMenuItem @click="handleDelete" class="text-destructive">
                                    <Trash class="mr-2 h-4 w-4" />
                                    Delete
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                </div>
            </CardHeader>

            <CardContent class="flex-1 overflow-y-auto p-4 sm:p-6">
                <div v-if="note.content" class="prose prose-sm text-muted-foreground dark:prose-invert max-w-none" v-html="formattedContent"></div>
                <p v-else class="text-muted-foreground italic">This note has no content.</p>

                <div v-if="note.related && note.related.length" class="mt-4 flex flex-wrap items-center gap-2 sm:mt-6" title="Related Items">
                    <p class="text-muted-foreground w-full text-sm font-medium">Related Items:</p>
                    <template v-for="item in note.related" :key="item.notable_id">
                        <Link :href="getLink(item)">
                            <Badge variant="outline">{{ item.type }}: {{ item.notable.title ?? item.notable_id }}</Badge>
                        </Link>
                    </template>
                </div>
            </CardContent>
        </Card>
    </div>

    <!-- Empty state when no note is selected -->
    <div v-else class="flex h-full items-center justify-center">
        <div class="text-center">
            <div class="bg-muted mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full">
                <StickyNote class="text-muted-foreground h-6 w-6" />
            </div>
            <h3 class="text-foreground mb-2 text-lg font-semibold">No note selected</h3>
            <p class="text-muted-foreground text-sm">Select a note from the list to view its details</p>
        </div>
    </div>
</template>

<style scoped>
:deep(.prose code) {
    background-color: hsl(var(--muted));
    padding: 0.125rem 0.25rem;
    border-radius: 0.25rem;
    font-size: 0.875rem;
}

:deep(.prose) {
    color: inherit;
}

:deep(.prose strong) {
    font-weight: 600;
}

:deep(.prose em) {
    font-style: italic;
}
</style>
