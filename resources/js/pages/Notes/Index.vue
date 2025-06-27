<script setup lang="ts">
import NoteCard from '@/components/notes/NoteCard.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { Note } from '@/types/model';
import { Head, router } from '@inertiajs/vue3';
import { Plus, StickyNote } from 'lucide-vue-next';

defineProps<{
    notes: Array<Note>;
}>();

// Note card event handlers
function handleView(note: Note) {
    router.visit(route('notes.show', note.id));
}

function handleEdit(note: Note) {
    router.visit(route('notes.edit', note.id));
}

function handleDelete(note: Note) {
    if (confirm('Are you sure you want to delete this note?')) {
        router.delete(route('notes.destroy', note.id));
    }
}

function handlePin(note: Note) {
    router.patch(route('notes.update', note.id), {
        is_pinned: !note.is_pinned,
    });
}
</script>

<template>
    <AppLayout
        :breadcrumbs="[
            {
                title: 'Notes',
                href: route('notes.index'),
            },
        ]"
    >
        <Head title="Notes" />

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Notes</h1>
                        <p class="text-muted-foreground">Organize your thoughts and ideas</p>
                    </div>
                    <Button>
                        <Plus class="mr-2 h-4 w-4" />
                        New Note
                    </Button>
                </div>

                <!-- Notes Grid -->
                <div v-if="notes.length > 0">
                    <!-- Pinned Notes -->
                    <div v-if="notes.filter((note) => note.is_pinned).length > 0" class="mb-8">
                        <h2 class="mb-4 text-lg font-semibold text-gray-700 dark:text-gray-300">Pinned Notes</h2>
                        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                            <NoteCard
                                v-for="note in notes.filter((note) => note.is_pinned)"
                                :key="note.id"
                                :note="note"
                                @view="handleView"
                                @edit="handleEdit"
                                @delete="handleDelete"
                                @pin="handlePin"
                            />
                        </div>
                    </div>

                    <!-- Regular Notes -->
                    <div v-if="notes.filter((note) => !note.is_pinned).length > 0">
                        <h2 class="mb-4 text-lg font-semibold text-gray-700 dark:text-gray-300">Notes</h2>
                        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                            <NoteCard
                                v-for="note in notes.filter((note) => !note.is_pinned)"
                                :key="note.id"
                                :note="note"
                                @view="handleView"
                                @edit="handleEdit"
                                @delete="handleDelete"
                                @pin="handlePin"
                            />
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <Card v-else class="py-12 text-center">
                    <CardContent>
                        <div class="bg-muted mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full">
                            <StickyNote class="text-muted-foreground h-6 w-6" />
                        </div>
                        <CardTitle class="mb-2">No notes yet</CardTitle>
                        <CardDescription class="mb-4"> Start organizing your thoughts by creating your first note. </CardDescription>
                        <Button>
                            <Plus class="mr-2 h-4 w-4" />
                            Create your first note
                        </Button>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
