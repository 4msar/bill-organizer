<script setup lang="ts">
import CategoryFormDialog from '@/components/CategoryFormDialog.vue';
import CategoryTable from '@/components/CategoryTable.vue';
import NoCategoriesWarning from '@/components/NoCategoriesWarning.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { Category as BaseCategory } from '@/types/model';
import { Head, router } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';
import { ref } from 'vue';

type Category = BaseCategory & {
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

// Open create dialog
function openCreateDialog() {
    isEditMode.value = false;
    selectedCategory.value = null;
    isOpen.value = true;
}

// Open edit dialog
function openEditDialog(category: Category) {
    isEditMode.value = true;
    selectedCategory.value = category;
    isOpen.value = true;
}

// Delete a category
function deleteCategory(id: number) {
    if (confirm('Are you sure you want to delete this category?')) {
        router.delete(route('categories.destroy', id));
    }
}
</script>

<template>
    <AppLayout
        :breadcrumbs="[
            {
                title: 'Categories',
                href: route('categories.index'),
            },
        ]"
    >
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

                <!-- Category Table -->
                <CategoryTable
                    :categories="props.categories"
                    @openEditDialog="openEditDialog"
                    @deleteCategory="deleteCategory"
                />

                <!-- Category Form Dialog -->
                <CategoryFormDialog
                    :isOpen="isOpen"
                    :isEditMode="isEditMode"
                    :dialogTitle="isEditMode ? 'Edit Category' : 'Create New Category'"
                    :dialogDescription="isEditMode ? 'Update the details of this category.' : 'Add a new category to organize your bills.'"
                    :availableIcons="props.availableIcons"
                    :category="selectedCategory"
                    v-on:update:open="isOpen=$event"
                />

                <!-- Warning for users with no categories -->
                <NoCategoriesWarning v-if="props.categories.length === 0" />
            </div>
        </div>
    </AppLayout>
</template>
