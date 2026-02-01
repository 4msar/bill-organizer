<script setup lang="ts">
import FormError from '@/components/shared/FormError.vue';
import MultiSelect from '@/components/shared/MultiSelect.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { SharedData } from '@/types';
import { Bill, Note } from '@/types/model';
import { type Page } from '@inertiajs/core';
import { useForm, usePage } from '@inertiajs/vue3';
import { Save, X } from 'lucide-vue-next';
import { watch } from 'vue';
import { Textarea } from '../ui/textarea';

const props = defineProps<{
    note?: Note | null;
    showActions?: boolean;
    actionSize?: 'sm' | 'icon' | 'default';
}>();

const emit = defineEmits<{
    submit: [];
    cancel: [];
    success: [data: Page<SharedData>];
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
    emit('submit');
    const options = {
        onSuccess: (data: Page) => {
            emit('success', data as Page<SharedData>);
            console.log('Form submission successful:', data);
            if (!props.note?.id) {
                form.reset();
            }
        },
    };

    if (props.note && props.note.id) {
        form.put(route('notes.update', props.note.id), {
            ...options,
            preserveState: false,
        });
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

defineExpose({
    form,
    submit: handleSubmit,
});
</script>

<template>
    <form @submit.prevent="handleSubmit" class="space-y-4">
        <div class="grid gap-4">
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
                    class="min-h-[220px]"
                    v-model="form.content"
                    :disabled="form.processing"
                />
                <span class="text-xs text-gray-500 italic">
                    You can use
                    <a href="https://www.markdownguide.org/basic-syntax/" target="_blank" rel="noopener noreferrer" class="underline"> Markdown</a>
                    syntax for formatting.
                </span>
                <FormError :message="form.errors.content" />
            </div>

            <div class="flex flex-row items-center justify-between rounded-lg border p-3">
                <div class="space-y-0.5">
                    <Label for="is_pinned" class="text-base">Pin this note</Label>
                    <div class="text-muted-foreground text-sm">Pinned notes appear at the top of your notes list.</div>
                </div>
                <Label for="is_pinned" class="flex items-center space-x-3">
                    <Checkbox id="is_pinned" v-model:checked="form.is_pinned" />
                </Label>
            </div>

            <div class="flex flex-row items-center justify-between rounded-lg border p-3">
                <div class="space-y-0.5">
                    <Label for="is_team_note" class="text-base">Team note</Label>
                    <div class="text-muted-foreground text-sm">Team notes are visible to all members of your team.</div>
                </div>
                <Label for="is_team_note" class="flex items-center space-x-3">
                    <Switch id="is_team_note" v-model:checked="form.is_team_note" />
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
        </div>

        <!-- Action Buttons (optional) -->
        <div v-if="showActions" class="flex items-center justify-end gap-2 pt-2">
            <Button
                type="button"
                variant="outline"
                :size="actionSize"
                @click="handleCancel"
                :disabled="form.processing"
                :class="actionSize === 'icon' ? 'sm:hidden' : 'hidden sm:flex'"
            >
                <X :class="actionSize !== 'icon' ? 'mr-2 h-4 w-4' : 'h-4 w-4'" />
                <span v-if="actionSize !== 'icon'">Cancel</span>
            </Button>
            <Button
                v-if="actionSize === 'icon'"
                type="button"
                variant="outline"
                size="icon"
                @click="handleCancel"
                :disabled="form.processing"
                class="sm:hidden"
            >
                <X class="h-4 w-4" />
            </Button>

            <Button type="submit" :size="actionSize" :disabled="form.processing" :class="actionSize === 'icon' ? 'sm:hidden' : 'hidden sm:flex'">
                <Save :class="actionSize !== 'icon' ? 'mr-2 h-4 w-4' : 'h-4 w-4'" />
                <span v-if="actionSize !== 'icon'">{{ form.processing ? 'Saving...' : 'Save' }}</span>
            </Button>
            <Button v-if="actionSize === 'icon'" type="submit" size="icon" :disabled="form.processing" class="sm:hidden">
                <Save class="h-4 w-4" />
            </Button>
        </div>
    </form>
</template>
