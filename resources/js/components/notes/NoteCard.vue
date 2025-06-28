<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { formatDate } from '@/lib/utils';
import { Note } from '@/types/model';
import { MoreVertical, Pencil, Pin, Trash } from 'lucide-vue-next';
import { computed } from 'vue';
import { Badge } from '../ui/badge';

const props = defineProps<{
    note: Note;
}>();

const emit = defineEmits<{
    view: [note: Note];
    edit: [note: Note];
    delete: [note: Note];
    pin: [note: Note];
}>();

const handleView = () => emit('view', props.note);
const handleEdit = () => emit('edit', props.note);
const handleDelete = () => emit('delete', props.note);
const handlePin = () => emit('pin', props.note);
const queryParams = new URLSearchParams(window.location.search);
const summaryText = computed(() => {
    if (!props.note.content) return '';

    // Remove markdown syntax and HTML tags for a clean summary
    const cleanContent = props.note.content
        .replace(/[#*`_~\[\]]/g, '') // Remove markdown symbols
        .replace(/<[^>]*>/g, '') // Remove HTML tags
        .replace(/\n+/g, ' ') // Replace newlines with spaces
        .trim();

    return cleanContent.length > 100 ? cleanContent.substring(0, 100) + '...' : cleanContent;
});
</script>

<template>
    <Card class="relative w-full max-w-sm gap-y-1 py-4 transition-shadow duration-200 hover:shadow-md">
        <CardHeader>
            <div class="flex items-start justify-between">
                <div class="flex w-full flex-wrap items-center justify-between">
                    <CardTitle class="line-clamp-2 pr-2 text-lg font-semibold">{{ note.title || 'Untitled Note' }} </CardTitle>
                    <Badge v-if="queryParams.get('team') === 'all'" class="pl-2">{{ note.team?.name }}</Badge>
                </div>
                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <Button variant="ghost" size="sm" class="h-8 w-8 p-0">
                            <MoreVertical class="h-4 w-4" />
                            <span class="sr-only">Open menu</span>
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                        <DropdownMenuItem @click="handleEdit">
                            <Pencil class="mr-2 h-4 w-4" />
                            Edit
                        </DropdownMenuItem>
                        <DropdownMenuItem @click="handlePin" :disabled="Boolean(note.is_pinned)">
                            <Pin class="mr-2 h-4 w-4" />
                            {{ note.is_pinned ? 'Pinned' : 'Pin' }}
                        </DropdownMenuItem>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem @click="handleDelete" class="text-destructive">
                            <Trash class="mr-2 h-4 w-4" />
                            Delete
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>
        </CardHeader>

        <CardContent class="pt-0 pb-1">
            <div class="border-muted-foreground/25 bg-muted/30 mb-4 rounded-md">
                <p class="text-muted-foreground line-clamp-3 text-sm">
                    {{ summaryText || 'No content available' }}
                </p>
            </div>

            <div v-if="note?.related && note.related.length" class="mt-4 flex items-center gap-2" title="Related Items">
                <Badge variant="secondary">Linked: {{ note.related.length }} items</Badge>
            </div>
        </CardContent>

        <CardFooter class="flex items-center justify-between">
            <time class="text-muted-foreground text-xs" :datetime="note.created_at">
                {{ formatDate(props.note.created_at) }}
            </time>

            <Button @click="handleView" variant="outline" size="sm" class="h-8"> View </Button>
        </CardFooter>

        <!-- Pin indicator -->
        <div v-if="note.is_pinned" class="absolute top-2 left-2">
            <Pin class="text-muted-foreground h-3 w-3" />
        </div>
    </Card>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
