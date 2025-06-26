<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Table, TableBody, TableCaption, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { formatCurrency, getIconComponent } from '@/lib/utils';
import { Category as BaseCategory } from '@/types/model';
import { Pencil, Trash2 } from 'lucide-vue-next';
import { defineProps } from 'vue';
import Confirm from './Confirm.vue';

interface Category extends BaseCategory {
    total_bills_count: number;
    unpaid_bills_count: number;
    total_amount: number | null;
    unpaid_amount: number | null;
}

defineProps<{
    categories: Category[];
}>();

defineEmits<{
    (e: 'openEditDialog', category: Category): void;
}>();
</script>

<template>
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
                                {{ formatCurrency(category.unpaid_amount, $page.props?.team?.current?.currency as string) }}
                            </span>
                            <span v-else class="text-muted-foreground">{{ formatCurrency(0, $page.props?.team?.current?.currency as string) }}</span>
                        </TableCell>
                        <TableCell class="max-w-[200px] truncate">
                            {{ category.description || '-' }}
                        </TableCell>
                        <TableCell class="text-right">
                            <div class="flex justify-end gap-2">
                                <Button variant="ghost" size="icon" @click="$emit('openEditDialog', category)">
                                    <Pencil class="h-4 w-4" />
                                    <span class="sr-only">Edit</span>
                                </Button>
                                <Confirm title="Are you sure?" :url="route('categories.destroy', category.id)">
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        :disabled="category.total_bills_count > 0"
                                        :title="category.total_bills_count > 0 ? 'Cannot delete categories with bills' : ''"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                        <span class="sr-only">Delete</span>
                                    </Button>
                                </Confirm>
                            </div>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </CardContent>
    </Card>
</template>
