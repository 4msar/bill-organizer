<script setup lang="ts">
import FormError from '@/components/shared/FormError.vue';
import MultiSelect from '@/components/shared/MultiSelect.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import { Bill, Note } from '@/types/model';
import { useForm, usePage } from '@inertiajs/vue3';
import { Save, X } from 'lucide-vue-next';
import { watch } from 'vue';

const props = defineProps<{
    note?: Note | null;
}>();

const emit = defineEmits<{
    cancel: [];
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
    related: props.note?.related?.map((item) => item.notable_id) || [],
});

const handleSubmit = async () => {
    const options = {
        onSuccess: () => {
            emit('success');
        },
    };

    if (props.note && props.note.id) {
        form.put(route('notes.update', props.note.id), options);
    } else {
        form.post(route('notes.store'), options);
    }
};

const handleCancel = () => {
    emit('cancel');
};

watch(
    () => props.note,
    (newNote) => {
        if (newNote) {
            form.title = newNote.title || '';
            form.content = newNote.content || '';
            form.is_pinned = Boolean(newNote.is_pinned || false);
            form.is_team_note = Boolean(newNote.user_id === null);
            form.related = newNote.related?.map((item) => item.notable_id) || [];
        } else {
            form.reset();
        }
    },
    { immediate: true },
);
</script>

<template>
    <div class="flex h-full flex-col">
        <Card class="flex h-full flex-col border-0 shadow-none">
            <CardHeader class="border-b">
                <div class="flex items-center justify-between gap-4">
                    <h2 class="text-lg font-semibold">
                        {{ note ? 'Edit Note' : 'Create New Note' }}
                    </h2>
                    <div class="flex items-center gap-2">
                        <Button variant="outline" size="sm" @click="handleCancel" :disabled="form.processing">
                            <X class="mr-2 h-4 w-4" />
                            Cancel
                        </Button>
                        <Button size="sm" @click="handleSubmit" :disabled="form.processing">
                            <Save class="mr-2 h-4 w-4" />
                            {{ form.processing ? 'Saving...' : 'Save' }}
                        </Button>
                    </div>
                </div>
            </CardHeader>

            <CardContent class="flex-1 overflow-y-auto p-6">
                <form @submit.prevent="handleSubmit" class="space-y-6">
                    <div>
                        <Label for="title" class="mb-2">Title *</Label>
                        <Input name="title" placeholder="Enter note title..." v-model="form.title" :disabled="form.processing" />
                        <FormError :message="form.errors.title" />
                    </div>

                    <div>
                        <Label for="content" class="mb-2">Content</Label>
                        <Textarea
                            name="content"
                            placeholder="Write your note content here..."
                            class="min-h-[300px] resize-none"
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
                            <Checkbox id="is_pinned" v-model="form.is_pinned" />
                        </Label>
                    </div>

                    <div class="flex flex-row items-center justify-between rounded-lg border p-3">
                        <div class="space-y-0.5">
                            <Label for="is_team_note" class="text-base">Team note</Label>
                            <div class="text-muted-foreground text-sm">Team notes are visible to all members of your team.</div>
                        </div>
                        <Label for="is_team_note" class="flex items-center space-x-3">
                            <Switch id="is_team_note" v-model="form.is_team_note" />
                        </Label>
                    </div>

                    <div>
                        <Label class="mb-2">Linked Bills</Label>
                        <MultiSelect
                            v-model="form.related"
                            :options="
                                bills.map((item) => ({
                                    label: item.title,
                                    value: item.id,
                                }))
                            "
                        />
                        <FormError :message="form.errors.related" />
                    </div>
                </form>
            </CardContent>
        </Card>
    </div>
</template>
