<script setup lang="ts">
import NoteForm from '@/components/notes/NoteForm.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { SharedData } from '@/types';
import { Note } from '@/types/model';
import { type Page } from '@inertiajs/core';
import { Save, X } from 'lucide-vue-next';
import { ref } from 'vue';

defineProps<{
    note?: Note | null;
}>();

const emit = defineEmits<{
    cancel: [];
    success: [data: Page<SharedData>];
}>();

const formRef = ref<InstanceType<typeof NoteForm>>();

const handleSuccess = (data: Page<SharedData>) => {
    console.log('Edit success data:', data);
    emit('success', data);
};

const handleCancel = () => {
    emit('cancel');
};
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
                        <Button variant="outline" size="sm" @click="handleCancel" :disabled="formRef?.form.processing" class="hidden sm:flex">
                            <X class="mr-2 h-4 w-4" />
                            Cancel
                        </Button>
                        <Button variant="outline" size="icon" @click="handleCancel" :disabled="formRef?.form.processing" class="sm:hidden">
                            <X class="h-4 w-4" />
                        </Button>
                        <Button size="sm" @click="formRef?.submit()" :disabled="formRef?.form.processing" class="hidden sm:flex">
                            <Save class="mr-2 h-4 w-4" />
                            {{ formRef?.form.processing ? 'Saving...' : 'Save' }}
                        </Button>
                        <Button size="icon" @click="formRef?.submit()" :disabled="formRef?.form.processing" class="sm:hidden">
                            <Save class="h-4 w-4" />
                        </Button>
                    </div>
                </div>
            </CardHeader>

            <CardContent class="flex-1 overflow-y-auto p-4 sm:p-6">
                <NoteForm ref="formRef" :note="note" @success="handleSuccess" @cancel="handleCancel" />
            </CardContent>
        </Card>
    </div>
</template>
