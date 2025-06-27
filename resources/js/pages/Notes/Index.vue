<script setup lang="ts">
import NoteCard from '@/components/notes/NoteCard.vue';
import NoteDialog from '@/components/notes/NoteDialog.vue';
import NoteViewDialog from '@/components/notes/NoteViewDialog.vue';
import Heading from '@/components/shared/Heading.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { Note } from '@/types/model';
import { Head, router } from '@inertiajs/vue3';
import { Filter, Plus, StickyNote } from 'lucide-vue-next';
import { ref } from 'vue';

defineProps<{
    notes: Array<Note>;
}>();

// Dialog states
const showCreateDialog = ref(false);
const showEditDialog = ref(false);
const showViewDialog = ref(false);
const selectedNote = ref<Note | undefined>(undefined);

// Note card event handlers
function handleView(note: Note) {
    selectedNote.value = note;
    showViewDialog.value = true;
}

function handleEdit(note: Note) {
    selectedNote.value = note;
    showEditDialog.value = true;
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

// Dialog event handlers
function handleCreateNote() {
    selectedNote.value = undefined;
    showCreateDialog.value = true;
}

function handleEditFromView(note: Note) {
    selectedNote.value = note;
    showViewDialog.value = false;
    showEditDialog.value = true;
}

function handleDeleteFromView(note: Note) {
    showViewDialog.value = false;
    handleDelete(note);
}

function handlePinFromView(note: Note) {
    handlePin(note);
}

function onDialogSuccess() {
    // Dialog will close automatically, Inertia will refresh the page data
}
const queryParams = new URLSearchParams(window.location.search);
const handleFilter = () => {
    router.visit(
        route('notes.index', {
            team: queryParams.get('team') === 'all' ? 'current' : 'all',
        }),
    );
};
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
                <Heading class="flex flex-wrap items-center justify-between gap-2" title="Notes" description="Organize your thoughts and ideas">
                    <div class="flex items-center gap-2">
                        <Button variant="secondary" @click="handleFilter">
                            <Filter class="mr-2 h-4 w-4" />
                            {{ queryParams.get('team') === 'all' ? 'Current Team Notes' : 'All Teams Notes' }}
                        </Button>
                        <Button @click="handleCreateNote">
                            <Plus class="mr-2 h-4 w-4" />
                            New Note
                        </Button>
                    </div>
                </Heading>

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
                        <Button @click="handleCreateNote">
                            <Plus class="mr-2 h-4 w-4" />
                            Create your first note
                        </Button>
                    </CardContent>
                </Card>
            </div>
        </div>

        <!-- Dialogs -->
        <NoteDialog v-model:open="showCreateDialog" @success="onDialogSuccess" />

        <NoteDialog v-model:open="showEditDialog" :note="selectedNote" @success="onDialogSuccess" />

        <NoteViewDialog
            v-model:open="showViewDialog"
            :note="selectedNote"
            @edit="handleEditFromView"
            @delete="handleDeleteFromView"
            @pin="handlePinFromView"
        />
    </AppLayout>
</template>
