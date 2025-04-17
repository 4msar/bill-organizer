
<script setup lang="ts">
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Form, FormControl, FormDescription, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { Button } from '@/components/ui/button';
import * as LucideIcons from 'lucide-vue-next';
import { defineProps, defineEmits, onMounted, watchEffect } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Category } from '@/types/model';

const props = defineProps<{
  isOpen: boolean;
  isEditMode: boolean;
  dialogTitle: string;
  dialogDescription: string;
  category: Category | null;
  availableIcons: Record<string, string>;
}>();

const emit = defineEmits<{
  'update:open': [value: boolean];
}>();

const form = useForm({
    id: null as number | null,
    name: '',
    description: '',
    icon: null as string | null,
});

watchEffect(() => {
    if (props.isEditMode && props.category) {
        form.id = props.category.id;
        form.name = props.category.name;
        form.description = props.category.description ?? '';
        form.icon = props.category.icon;
    }
})

function handleClose(value: boolean) {
  if(value === false) form.reset();
  emit('update:open', value);
}

// Handle form submission
function submitForm() {
    if (props.isEditMode && form.id) {
        form.put(route('categories.update', form.id), {
            onSuccess: () => {
                handleClose(false);
            },
        });
    } else {
        form.post(route('categories.store'), {
            onSuccess: () => {
              handleClose(false);
            },
        });
    }
}

function getIconComponent(iconName: string | null) {
  if (!iconName) return LucideIcons.CircleDot;

  const pascalCase = iconName
    .split('-')
    .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
    .join('');

  return LucideIcons[pascalCase as keyof typeof LucideIcons] || LucideIcons.CircleDot;
}
</script>

<template>
  <Dialog :open="isOpen" @update:open="handleClose">
    <DialogContent class="sm:max-w-[500px]">
      <DialogHeader>
        <DialogTitle>{{ dialogTitle }}</DialogTitle>
        <DialogDescription>
          {{ dialogDescription }}
        </DialogDescription>
      </DialogHeader>

      <Form @submit="submitForm">
        <div class="grid gap-4 py-4">
          <FormField v-model="form.name" name="name">
            <FormItem>
              <FormLabel>Name</FormLabel>
              <FormControl>
                <Input v-model="form.name" placeholder="Category Name" />
              </FormControl>
              <FormMessage />
            </FormItem>
          </FormField>

          <FormField v-model="form.icon" name="icon">
            <FormItem>
              <FormLabel>Icon</FormLabel>
              <FormControl>
                <Select v-model="form.icon">
                  <SelectTrigger class="w-full">
                    <SelectValue placeholder="Select an icon">
                      <div class="flex items-center">
                        <component :is="getIconComponent(form.icon)" class="mr-2 h-4 w-4" />
                        <span>{{ form.icon ? availableIcons[form.icon] : 'No Icon' }}</span>
                      </div>
                    </SelectValue>
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem :value="null">No Icon</SelectItem>
                    <SelectItem v-for="(label, value) in availableIcons" :key="value" :value="value">
                      <div class="flex items-center">
                        <component :is="getIconComponent(value)" class="mr-2 h-4 w-4" />
                        <span>{{ label }}</span>
                      </div>
                    </SelectItem>
                  </SelectContent>
                </Select>
              </FormControl>
              <FormDescription> Choose an icon that represents this category. </FormDescription>
            </FormItem>
          </FormField>

          <FormField v-model="form.description" name="description">
            <FormItem>
              <FormLabel>Description (Optional)</FormLabel>
              <FormControl>
                <Textarea v-model="form.description" placeholder="Enter a brief description of this category" rows="3" />
              </FormControl>
            </FormItem>
          </FormField>
        </div>

        <DialogFooter>
          <Button type="submit" :disabled="form.processing">
            {{ isEditMode ? 'Update' : 'Create' }}
          </Button>
        </DialogFooter>
      </Form>
    </DialogContent>
  </Dialog>
</template>
