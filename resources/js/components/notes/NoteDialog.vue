<script setup lang="ts">
import NoteForm from '@/components/notes/NoteForm.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Note } from '@/types/model';
import { ref } from 'vue';

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

const formRef = ref<InstanceType<typeof NoteForm>>();

const handleSuccess = () => {
    emit('update:open', false);
    emit('success');
};

const handleClose = () => {
    if (!formRef.value?.form.processing) {
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

            <div class="py-4">
                <NoteForm ref="formRef" :note="note" @success="handleSuccess" />
            </div>

            <DialogFooter>
                <Button type="button" variant="outline" @click="handleClose" :disabled="formRef?.form.processing"> Cancel </Button>
                <Button type="button" @click="formRef?.submit()" :disabled="formRef?.form.processing">
                    {{ formRef?.form.processing ? 'Saving...' : !props.note?.id ? 'Create Note' : 'Save Changes' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
