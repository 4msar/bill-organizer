<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import { Bill, Note } from '@/types/model';
import { useForm, usePage } from '@inertiajs/vue3';
import { watch } from 'vue';
import FormError from '../shared/FormError.vue';
import MultiSelect from '../shared/MultiSelect.vue';
import { Label } from '../ui/label';

interface Props {
    open: boolean;
    note?: Note;
}

const props = withDefaults(defineProps<Props>(), {
    note: undefined,
});

const emit = defineEmits<{
    'update:open': [value: boolean];
    success: [];
}>();

const {
    props: { bills },
} = usePage<{ bills: Bill[] }>();

const form = useForm({
    title: props.note?.title || '',
    content: props.note?.content || '',
    is_pinned: Boolean(props.note?.is_pinned || false),
    is_team_note: Boolean(props.note?.user_id === null),
    related: props.note?.related?.map(item=>item.notable_id) || [],
});

const handleSubmit = async () => {
    const options = {
        onSuccess: () => {
            emit('update:open', false);
            emit('success');
            form.reset();
        },
    };
    if (props.note && props.note.id) {
        form.put(route('notes.update', props.note.id), options);
    } else {
        form.post(route('notes.store'), options);
    }
};

watch(
    () => props.note,
    (newNote) => {
        if (newNote) {
            form.title = newNote.title || '';
            form.content = newNote.content || '';
            form.is_pinned = Boolean(newNote.is_pinned || false);
            form.is_team_note = Boolean(newNote.user_id === null);
            form.related = newNote.related?.map(item=>item.notable_id) || [];
        } else {
            form.reset();
        }
    },
    { immediate: true },
);

const handleClose = () => {
    if (!form.processing) {
        emit('update:open', false);
    }
};
</script>

<template>
    <Dialog :open="open" @update:open="handleClose">
        <DialogContent class="sm:max-w-[525px]">
            <DialogHeader>
                <DialogTitle>
                    {{ !props.note?.id ? 'Create New Note' : 'Edit Note' }}
                </DialogTitle>
                <DialogDescription>
                    {{ !props.note?.id ? 'Create a new note to organize your thoughts and ideas.' : 'Make changes to your note here.' }}
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="handleSubmit">
                <div class="grid gap-4 py-4">
                    <div>
                        <Label class="mb-2">Title *</Label>
                        <Input placeholder="Enter note title..." v-model="form.title" :disabled="form.processing" />
                        <FormError :message="form.errors.title" />
                    </div>

                    <div>
                        <Label class="mb-2">Content</Label>
                        <Textarea
                            placeholder="Write your note content here..."
                            class="min-h-[120px] resize-none"
                            v-model="form.content"
                            :disabled="form.processing"
                        />
                        <FormError :message="form.errors.content" />
                    </div>

                    <div class="flex flex-row items-center justify-between rounded-lg border p-3">
                        <div class="space-y-0.5">
                            <Label for="is_pinned" class="text-base">Pin this note</Label>
                            <div class="text-muted-foreground text-sm">Pinned notes appear at the top of your notes list.</div>
                        </div>
                        <Label for="is_pinned" class="flex items-center space-x-3">
                            <Checkbox id="is_pinned" v-model="form.is_pinned" :tabindex="3" />
                        </Label>
                    </div>

                    <div class="flex flex-row items-center justify-between rounded-lg border p-3">
                        <div class="space-y-0.5">
                            <Label for="is_team_note" class="text-base">Team note</Label>
                            <div class="text-muted-foreground text-sm">Team notes are visible to all members of your team.</div>
                        </div>
                        <Label for="is_team_note" class="flex items-center space-x-3">
                            <Switch id="is_team_note" v-model="form.is_team_note" :tabindex="4" />
                        </Label>
                    </div>

                    <div>
                        <Label class="mb-2">Linked Bill</Label>
                        <MultiSelect
                            v-model="form.related"
                            :options="
                                bills.map((item) => ({
                                    label: item.title,
                                    value: item.id,
                                }))
                            "
                        />
                        <FormError :message="form.errors.title" />
                    </div>
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="handleClose" :disabled="form.processing"> Cancel </Button>
                    <Button type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Saving...' : !props.note?.id ? 'Create Note' : 'Save Changes' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
