<script setup lang="ts">
import NoteCard from '@/components/notes/NoteCard.vue';
import NoteDetailView from '@/components/notes/NoteDetailView.vue';
import NoteDialog from '@/components/notes/NoteDialog.vue';
import NoteEditView from '@/components/notes/NoteEditView.vue';
import NoteListItem from '@/components/notes/NoteListItem.vue';
import NoteViewDialog from '@/components/notes/NoteViewDialog.vue';
import Heading from '@/components/shared/Heading.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardTitle } from '@/components/ui/card';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { ExportFormat, useNoteExport } from '@/composables/useNoteExport';
import AppLayout from '@/layouts/AppLayout.vue';
import type { SharedData } from '@/types';
import { Note } from '@/types/model';
import { Head, router, usePage } from '@inertiajs/vue3';
import { Download, Filter, Plus, Search, StickyNote } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';

const props = defineProps<{
    notes: Array<Note>;
}>();

const { exportNotes } = useNoteExport();

const page = usePage<SharedData>();
const user = page.props.auth.user;
const viewMode = computed(() => (user.metas?.notes_view_mode as string) ?? 'grid');

// Dialog states (for grid view)
const showCreateDialog = ref(false);
const showEditDialog = ref(false);
const showViewDialog = ref(false);

// Sidebar view states
const selectedNote = ref<Note | undefined>(undefined);
const isEditing = ref(false);
const searchQuery = ref('');

// Filtered and sorted notes
const filteredNotes = computed(() => {
    if (!searchQuery.value) return props.notes;

    const query = searchQuery.value.toLowerCase();
    return props.notes.filter((note) => note.title?.toLowerCase().includes(query) || note.content?.toLowerCase().includes(query));
});

const pinnedNotes = computed(() => filteredNotes.value.filter((note) => note.is_pinned));
const regularNotes = computed(() => filteredNotes.value.filter((note) => !note.is_pinned));

// Note card event handlers (grid view)
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
        router.delete(route('notes.destroy', note.id), {
            onSuccess: () => {
                // Clear selection if deleted note was selected
                if (selectedNote.value?.id === note.id) {
                    selectedNote.value = undefined;
                    isEditing.value = false;
                }
            },
        });
    }
}

function handlePin(note: Note) {
    router.patch(route('notes.update', note.id), {
        is_pinned: !note.is_pinned,
    });
}

// Dialog event handlers (grid view)
function handleCreateNote() {
    if (viewMode.value === 'sidebar') {
        selectedNote.value = undefined;
        isEditing.value = true;
    } else {
        selectedNote.value = undefined;
        showCreateDialog.value = true;
    }
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

// Sidebar view handlers
function handleSelectNote(note: Note) {
    selectedNote.value = note;
    isEditing.value = false;
}

function handleEditNote(note: Note) {
    selectedNote.value = note;
    isEditing.value = true;
}

function handleDeleteNote(note: Note) {
    handleDelete(note);
}

function handlePinNote(note: Note) {
    handlePin(note);
}

function handleCancelEdit() {
    isEditing.value = false;
    // Keep the note selected in view mode
}

function handleEditSuccess() {
    isEditing.value = false;
    // Keep the note selected to view the updated version
}

function handleExport(format: ExportFormat) {
    exportNotes(props.notes, format);
}

const queryParams = new URLSearchParams(window.location.search);
const handleFilter = () => {
    router.visit(
        route('notes.index', {
            team: queryParams.get('team') === 'all' ? 'current' : 'all',
        }),
    );
};

onMounted(() => {
    // If in sidebar view and there are notes, select the first one by default
    if (viewMode.value === 'sidebar' && props.notes.length > 0 && !selectedNote.value) {
        handleSelectNote(props.notes[0]);
    }
});
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

        <!-- Grid View -->
        <div v-if="viewMode === 'grid'" class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <Heading class="flex flex-wrap items-center justify-between gap-2" title="Notes" description="Organize your thoughts and ideas">
                    <div class="flex items-center gap-2">
                        <Button variant="secondary" @click="handleFilter">
                            <Filter class="mr-2 h-4 w-4" />
                            {{ queryParams.get('team') === 'all' ? 'Current Team Notes' : 'All Teams Notes' }}
                        </Button>
                        <DropdownMenu v-if="notes.length > 0">
                            <DropdownMenuTrigger as-child>
                                <Button variant="outline">
                                    <Download class="mr-2 h-4 w-4" />
                                    Export Notes
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                                <DropdownMenuItem @click="handleExport('json')"> Export as JSON </DropdownMenuItem>
                                <DropdownMenuItem @click="handleExport('markdown')"> Export as Markdown </DropdownMenuItem>
                                <DropdownMenuItem @click="handleExport('csv')"> Export as CSV </DropdownMenuItem>
                                <DropdownMenuItem @click="handleExport('txt')"> Export as Text </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
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

        <!-- Sidebar View -->
        <div v-else class="flex h-[calc(100vh-5rem)] flex-col py-6">
            <div class="mx-auto w-full max-w-7xl flex-1 px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <Heading class="mb-4 flex flex-wrap items-center justify-between gap-2" title="Notes" description="Organize your thoughts and ideas">
                    <div class="flex items-center gap-2">
                        <Button variant="secondary" @click="handleFilter">
                            <Filter class="mr-2 h-4 w-4" />
                            {{ queryParams.get('team') === 'all' ? 'Current Team Notes' : 'All Teams Notes' }}
                        </Button>
                        <DropdownMenu v-if="notes.length > 0">
                            <DropdownMenuTrigger as-child>
                                <Button variant="outline">
                                    <Download class="mr-2 h-4 w-4" />
                                    Export Notes
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                                <DropdownMenuItem @click="handleExport('json')"> Export as JSON </DropdownMenuItem>
                                <DropdownMenuItem @click="handleExport('markdown')"> Export as Markdown </DropdownMenuItem>
                                <DropdownMenuItem @click="handleExport('csv')"> Export as CSV </DropdownMenuItem>
                                <DropdownMenuItem @click="handleExport('txt')"> Export as Text </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                        <Button @click="handleCreateNote">
                            <Plus class="mr-2 h-4 w-4" />
                            New Note
                        </Button>
                    </div>
                </Heading>

                <!-- Sidebar Layout -->
                <div class="flex h-[calc(100vh-12rem)] gap-4">
                    <!-- Left Sidebar - Notes List -->
                    <Card class="w-80 flex-shrink-0 overflow-hidden">
                        <div class="border-b p-4">
                            <div class="relative">
                                <Search class="text-muted-foreground absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2" />
                                <Input v-model="searchQuery" placeholder="Search notes..." class="pl-9" />
                            </div>
                        </div>
                        <div class="overflow-y-auto">
                            <div v-if="filteredNotes.length > 0">
                                <!-- Pinned Notes -->
                                <div v-if="pinnedNotes.length > 0">
                                    <div class="bg-muted/50 px-4 py-2 text-xs font-semibold tracking-wider uppercase">Pinned</div>
                                    <NoteListItem
                                        v-for="note in pinnedNotes"
                                        :key="note.id"
                                        :note="note"
                                        :is-active="selectedNote?.id === note.id"
                                        :show-team-badge="queryParams.get('team') === 'all'"
                                        @select="handleSelectNote"
                                    />
                                </div>

                                <!-- Regular Notes -->
                                <div v-if="regularNotes.length > 0">
                                    <div class="bg-muted/50 px-4 py-1 text-xs font-semibold tracking-wider uppercase">Notes</div>
                                    <NoteListItem
                                        v-for="note in regularNotes"
                                        :key="note.id"
                                        :note="note"
                                        :is-active="selectedNote?.id === note.id"
                                        :show-team-badge="queryParams.get('team') === 'all'"
                                        @select="handleSelectNote"
                                    />
                                </div>
                            </div>

                            <!-- Empty State in Sidebar -->
                            <div v-else class="flex h-full items-center justify-center p-8 text-center">
                                <div>
                                    <StickyNote class="text-muted-foreground mx-auto mb-2 h-8 w-8" />
                                    <p class="text-muted-foreground text-sm">
                                        {{ searchQuery ? 'No notes found' : 'No notes yet' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </Card>

                    <!-- Right Panel - Note Details or Edit -->
                    <div class="flex-1 overflow-hidden">
                        <NoteEditView v-if="isEditing" :note="selectedNote" @cancel="handleCancelEdit" @success="handleEditSuccess" />
                        <NoteDetailView v-else :note="selectedNote" @edit="handleEditNote" @delete="handleDeleteNote" @pin="handlePinNote" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Dialogs (for grid view) -->
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
