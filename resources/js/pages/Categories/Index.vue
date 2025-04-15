<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Form, FormControl, FormDescription, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCaption, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatCurrency } from '@/lib/utils';
import { Head, router, useForm } from '@inertiajs/vue3';
import * as LucideIcons from 'lucide-vue-next';
import { AlertCircle, Pencil, Plus, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Category {
    id: number;
    name: string;
    description: string | null;
    icon: string | null;
    total_bills_count: number;
    unpaid_bills_count: number;
    total_amount: number | null;
    unpaid_amount: number | null;
}

interface Props {
    categories: Category[];
    availableIcons: Record<string, string>;
}

const props = defineProps<Props>();

// For create/edit dialog
const isOpen = ref(false);
const isEditMode = ref(false);
const selectedCategory = ref<Category | null>(null);

const form = useForm({
    id: null as number | null,
    name: '',
    description: '',
    icon: null as string | null,
});

const dialogTitle = computed(() => {
    return isEditMode.value ? 'Edit Category' : 'Create New Category';
});

const dialogDescription = computed(() => {
    return isEditMode.value ? 'Update the details of this category.' : 'Add a new category to organize your bills.';
});

// Open create dialog
function openCreateDialog() {
    form.reset();
    isEditMode.value = false;
    selectedCategory.value = null;
    isOpen.value = true;
}

// Open edit dialog
function openEditDialog(category: Category) {
    form.id = category.id;
    form.name = category.name;
    form.description = category.description || '';
    form.icon = category.icon;

    isEditMode.value = true;
    selectedCategory.value = category;
    isOpen.value = true;
}

// Handle form submission
function submitForm() {
    if (isEditMode.value && form.id) {
        form.put(route('categories.update', form.id), {
            onSuccess: () => {
                isOpen.value = false;
            },
        });
    } else {
        form.post(route('categories.store'), {
            onSuccess: () => {
                isOpen.value = false;
            },
        });
    }
}

// Delete a category
function deleteCategory(id: number) {
    if (confirm('Are you sure you want to delete this category?')) {
        router.delete(route('categories.destroy', id));
    }
}

// Get icon component dynamically
function getIconComponent(iconName: string | null) {
    if (!iconName) return LucideIcons.CircleDot;

    // Convert kebab-case to PascalCase for Lucide icons
    const pascalCase = iconName
        .split('-')
        .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
        .join('');

    return LucideIcons[pascalCase as keyof typeof LucideIcons] || LucideIcons.CircleDot;
}
</script>

<template>
    <AppLayout>
        <Head title="Categories" />

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Header with Add New Button -->
                <div class="mb-6 flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Categories</h2>
                    <Button @click="openCreateDialog">
                        <Plus class="mr-2 h-4 w-4" />
                        Add Category
                    </Button>
                </div>

                <Card>
                    <CardContent class="pt-6">
                        <Table>
                            <TableCaption v-if="categories.length === 0"> You haven't created any categories yet. </TableCaption>
                            <TableHeader>
                                <TableRow>
                                    <TableHead class="w-12"></TableHead>
                                    <TableHead>Name</TableHead>
                                    <TableHead>Bills</TableHead>
                                    <TableHead>Unpaid Amount</TableHead>
                                    <TableHead>Description</TableHead>
                                    <TableHead class="text-right">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="category in categories" :key="category.id">
                                    <TableCell>
                                        <component :is="getIconComponent(category.icon)" class="h-5 w-5" />
                                    </TableCell>
                                    <TableCell class="font-medium">{{ category.name }}</TableCell>
                                    <TableCell>
                                        <Badge variant="secondary">
                                            {{ category.total_bills_count }}
                                        </Badge>
                                        <span class="text-muted-foreground ml-2 text-sm" v-if="category.unpaid_bills_count > 0">
                                            ({{ category.unpaid_bills_count }} unpaid)
                                        </span>
                                    </TableCell>
                                    <TableCell>
                                        <span v-if="category.unpaid_amount" class="font-medium">
                                            {{ formatCurrency(category.unpaid_amount) }}
                                        </span>
                                        <span v-else class="text-muted-foreground">$0.00</span>
                                    </TableCell>
                                    <TableCell class="max-w-[200px] truncate">
                                        {{ category.description || '-' }}
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <div class="flex justify-end gap-2">
                                            <Button variant="ghost" size="icon" @click="openEditDialog(category)">
                                                <Pencil class="h-4 w-4" />
                                                <span class="sr-only">Edit</span>
                                            </Button>
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                @click="deleteCategory(category.id)"
                                                :disabled="category.total_bills_count > 0"
                                                :title="category.total_bills_count > 0 ? 'Cannot delete categories with bills' : ''"
                                            >
                                                <Trash2 class="h-4 w-4" />
                                                <span class="sr-only">Delete</span>
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>

                <!-- Category Form Dialog -->
                <Dialog v-model:open="isOpen">
                    <DialogContent class="sm:max-w-[500px]">
                        <DialogHeader>
                            <DialogTitle>{{ dialogTitle }}</DialogTitle>
                            <DialogDescription>
                                {{ dialogDescription }}
                            </DialogDescription>
                        </DialogHeader>

                        <Form @submit="submitForm">
                            <div class="grid gap-4 py-4">
                                <!-- Name Field -->
                                <FormField v-model="form.name" name="name" :validate="(v) => !!v || 'Name is required'">
                                    <FormItem>
                                        <FormLabel>Name</FormLabel>
                                        <FormControl>
                                            <Input v-model="form.name" placeholder="Category Name" />
                                        </FormControl>
                                        <FormMessage />
                                    </FormItem>
                                </FormField>

                                <!-- Icon Field -->
                                <FormField v-model="form.icon" name="icon">
                                    <FormItem>
                                        <FormLabel>Icon</FormLabel>
                                        <FormControl>
                                            <Select v-model="form.icon">
                                                <SelectTrigger>
                                                    <SelectValue placeholder="Select an icon" />
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

                                <!-- Description Field -->
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

                <!-- Warning for users with no categories -->
                <div v-if="categories.length === 0" class="mt-6">
                    <div class="flex rounded-md border border-yellow-200 bg-yellow-50 p-4 dark:border-yellow-800 dark:bg-yellow-900/20">
                        <AlertCircle class="mt-0.5 mr-3 h-5 w-5 text-yellow-600 dark:text-yellow-500" />
                        <div>
                            <h3 class="font-medium text-yellow-800 dark:text-yellow-500">No categories found</h3>
                            <p class="mt-1 text-sm text-yellow-700 dark:text-yellow-400">
                                Categories help you organize your bills and track expenses by type. Click the "Add Category" button to create your
                                first category.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
