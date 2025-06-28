<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { formatDate, getLink } from '@/lib/utils';
import { Note } from '@/types/model';
import { Link } from '@inertiajs/vue3';
import { Pencil, Pin, Trash } from 'lucide-vue-next';
import { computed } from 'vue';
import { Badge } from '../ui/badge';

interface Props {
    open: boolean;
    note?: Note | null;
}

const props = withDefaults(defineProps<Props>(), {
    note: null,
});

const emit = defineEmits<{
    'update:open': [value: boolean];
    edit: [note: Note];
    delete: [note: Note];
    pin: [note: Note];
}>();

const handleClose = () => {
    emit('update:open', false);
};

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

const formattedContent = computed(() => {
    if (!props.note?.content) return '';

    // Simple markdown-like formatting
    return props.note.content
        .replace(/\n/g, '<br>')
        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
        .replace(/\*(.*?)\*/g, '<em>$1</em>')
        .replace(/`(.*?)`/g, '<code class="bg-muted px-1 py-0.5 rounded text-sm">$1</code>');
});
</script>

<template>
    <Dialog :open="open" @update:open="handleClose">
        <DialogContent class="max-h-[80vh] overflow-y-auto sm:max-w-[600px]">
            <DialogHeader>
                <div class="flex items-start justify-between">
                    <div class="flex-1 pr-4">
                        <DialogTitle class="text-xl">
                            {{ note?.title || 'Untitled Note' }}
                        </DialogTitle>
                        <DialogDescription>
                            <div class="mt-1 flex items-center gap-2">
                                <span>Created {{ formatDate(note?.created_at || '') }}</span>
                                <Pin v-if="note?.is_pinned" class="text-muted-foreground h-3 w-3" />
                            </div>
                        </DialogDescription>
                    </div>
                </div>
            </DialogHeader>

            <div class="py-4">
                <div v-if="note?.content" class="prose prose-sm text-muted-foreground max-w-none" v-html="formattedContent"></div>
                <p v-else class="text-muted-foreground italic">This note has no content.</p>

                <div v-if="note?.related && note.related.length" class="mt-4 flex items-center gap-2" title="Related Items">
                    <template v-for="item in note.related" :key="item.notable_id">
                        <Link :href="getLink(item)">
                            <Badge>{{ item.type }}:{{ item.notable_id }}</Badge>
                        </Link>
                    </template>
                </div>
            </div>

            <DialogFooter class="flex justify-between sm:justify-between">
                <div class="flex gap-2">
                    <Button variant="outline" size="sm" @click="handleEdit">
                        <Pencil class="mr-2 h-4 w-4" />
                        Edit
                    </Button>
                    <Button variant="outline" size="sm" @click="handlePin">
                        <Pin class="mr-2 h-4 w-4" />
                        {{ note?.is_pinned ? 'Unpin' : 'Pin' }}
                    </Button>
                    <Button variant="outline" size="sm" @click="handleDelete" class="text-destructive hover:text-destructive">
                        <Trash class="mr-2 h-4 w-4" />
                        Delete
                    </Button>
                </div>
                <Button @click="handleClose"> Close </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
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
