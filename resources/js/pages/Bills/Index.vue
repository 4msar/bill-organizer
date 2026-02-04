<script setup lang="ts">
import SortableTableHead from '@/components/bills/SortableTableHead.vue';
import Confirm from '@/components/shared/Confirm.vue';
import Pagination from '@/components/shared/Pagination.vue';
import SearchForm from '@/components/shared/SearchForm.vue';
import { Badge, BadgeVariants } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCaption, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatCurrency, formatDate, getVariantByStatus } from '@/lib/utils';
import { PaginationData } from '@/types';
import { Bill, Category } from '@/types/model';
import { Head, Link, router } from '@inertiajs/vue3';
import { Calendar, CheckCheck, Clock, DollarSign, Edit, Eye, MoreHorizontal, PlusCircle, Trash2 } from 'lucide-vue-next';
import { capitalize } from 'vue';

defineProps<{
    bills: PaginationData<Bill[]>;
    total_unpaid: number;
    unpaid_count: number;
    upcoming_count: number;
    paid_count: number;
    categories: Category[];
}>();

function markAsPaid(id: string | number) {
    router.patch(route('bills.pay', id));
}
</script>

<template>
    <AppLayout
        :breadcrumbs="[
            {
                title: 'Bills',
                href: route('bills.index'),
            },
        ]"
    >
        <Head title="Bills" />

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Summary Cards -->
                <h2 class="col-span-1 mb-2 text-lg text-gray-500 dark:text-gray-200">Current Month Stats</h2>
                <div class="mb-6 grid grid-cols-1 gap-6 md:grid-cols-3">
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium"> Total Unpaid Bills </CardTitle>
                            <DollarSign class="text-muted-foreground h-4 w-4" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">
                                {{ formatCurrency(total_unpaid, $page.props?.team?.current?.currency as string) }}
                            </div>
                            <p class="text-muted-foreground text-xs">{{ unpaid_count }} unpaid bill{{ unpaid_count !== 1 ? 's' : '' }}</p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium"> Upcoming Bills </CardTitle>
                            <Calendar class="text-muted-foreground h-4 w-4" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">{{ upcoming_count }}</div>
                            <p class="text-muted-foreground text-xs">Due in the next 7 days</p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium"> Paid Bills </CardTitle>
                            <Clock class="text-muted-foreground h-4 w-4" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">{{ paid_count }}</div>
                            <p class="text-muted-foreground text-xs">Previously paid bills</p>
                        </CardContent>
                    </Card>
                </div>

                <!-- Bills Header with New Bill Button -->
                <div class="mb-6 flex flex-wrap items-start justify-between gap-3 md:items-center">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Bills</h2>
                    <div class="flex flex-wrap items-center gap-2">
                        <SearchForm :url="route('bills.index')" class="col-auto flex w-auto flex-1/4 flex-wrap items-center gap-2 md:flex-auto">
                            <template #default="{ searchHandler, queryParams }">
                                <Select
                                    :default-value="queryParams.category"
                                    v-on:update:model-value="(value) => searchHandler({ category: (value as string) ?? '' })"
                                    class="w-full"
                                >
                                    <SelectTrigger class="w-full sm:w-fit">
                                        <SelectValue placeholder="Select a category" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem :value="null">All</SelectItem>
                                        <SelectItem v-for="category in categories" :key="category.id" :value="`${category.id}`">
                                            {{ category.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <Select
                                    :default-value="queryParams.status"
                                    v-on:update:model-value="(value) => searchHandler({ status: (value as string) ?? '' })"
                                    class="w-full"
                                >
                                    <SelectTrigger class="w-full sm:w-fit">
                                        <SelectValue placeholder="Select status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem :value="null">All</SelectItem>
                                        <SelectItem value="paid">Paid</SelectItem>
                                        <SelectItem value="unpaid">Unpaid</SelectItem>
                                        <SelectItem value="overdue">Overdue</SelectItem>
                                        <SelectItem value="upcoming">Upcoming</SelectItem>
                                    </SelectContent>
                                </Select>
                                <!-- Calendar picker with month and year only -->
                                <Input
                                    type="month"
                                    class="w-full sm:w-fit"
                                    :value="queryParams.date"
                                    :default-value="queryParams.date"
                                    @update:model-value="(value) => searchHandler({ date: value })"
                                />

                                <Link :href="route('bills.create')">
                                    <Button type="button">
                                        <PlusCircle class="mr-2 h-4 w-4" />
                                        Add New Bill
                                    </Button>
                                </Link>
                            </template>
                        </SearchForm>
                    </div>
                </div>

                <!-- Bills Tabs and Table -->
                <Card>
                    <CardHeader>
                        <CardTitle>All Bills</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableCaption v-if="bills.data.length === 0">You don't have any bills yet.</TableCaption>
                            <TableHeader>
                                <TableRow>
                                    <SortableTableHead field="title" label="Title" />
                                    <SortableTableHead field="category_id" label="Category" />
                                    <SortableTableHead field="amount" label="Amount" />
                                    <SortableTableHead field="due_date" label="Due Date" default />
                                    <SortableTableHead field="status" label="Status" />
                                    <TableHead class="text-right">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow
                                    v-for="bill in bills.data"
                                    :key="bill.id"
                                    @click.stop="router.visit(route('bills.show', bill.slug))"
                                    class="hover:bg-muted/50 cursor-pointer"
                                >
                                    <TableCell class="font-medium">
                                        {{ bill.title }}
                                        <div class="mt-1 flex flex-wrap gap-1">
                                            <Badge v-if="bill.is_recurring" variant="outline" class="text-xs">Recurring </Badge>
                                            <Badge v-if="bill.has_trial" variant="outline" class="border-blue-200 bg-blue-50 text-xs text-blue-700"
                                                >Trial</Badge
                                            >
                                        </div>
                                    </TableCell>
                                    <TableCell>{{ bill.category?.name || 'Uncategorized' }}</TableCell>
                                    <TableCell>{{ formatCurrency(bill.amount, $page.props?.team?.current?.currency as string) }}</TableCell>
                                    <TableCell>
                                        <div>
                                            <span
                                                :class="{
                                                    'text-amber-600': bill.status === 'overdue',
                                                    'text-destructive': bill.status === 'unpaid',
                                                }"
                                            >
                                                {{ formatDate(bill.due_date as string) }}
                                            </span>
                                            <div v-if="bill.has_trial && bill.trial_end_date" class="text-muted-foreground text-xs">
                                                Trial ends: {{ formatDate(bill.trial_end_date) }}
                                            </div>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <Badge
                                            :class="{
                                                'border-amber-300 text-amber-600': bill.status === 'overdue',
                                            }"
                                            :variant="getVariantByStatus<BadgeVariants['variant']>(bill.status)"
                                        >
                                            {{ capitalize(bill.status) }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <DropdownMenu>
                                            <DropdownMenuTrigger as="button" @click.stop class="p-2">
                                                <span class="sr-only">Open menu</span>
                                                <MoreHorizontal class="h-4 w-4" />
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end">
                                                <DropdownMenuItem>
                                                    <Link :href="route('bills.show', bill.slug)" class="flex w-full items-center gap-2">
                                                        <Eye class="mr-2 h-4 w-4" />
                                                        View details
                                                    </Link>
                                                </DropdownMenuItem>
                                                <DropdownMenuItem class="flex items-center">
                                                    <Link :href="route('bills.edit', bill.slug)" class="flex w-full items-center gap-2">
                                                        <Edit class="mr-2 h-4 w-4" />
                                                        Edit
                                                    </Link>
                                                </DropdownMenuItem>
                                                <DropdownMenuItem
                                                    v-if="bill.status === 'unpaid'"
                                                    @click.stop="markAsPaid(bill.id)"
                                                    class="flex items-center"
                                                >
                                                    <CheckCheck class="mr-2 h-4 w-4" />
                                                    Mark as paid
                                                </DropdownMenuItem>
                                                <Confirm :modal="true" title="Are you sure?" :url="route('bills.destroy', bill.slug)">
                                                    <DropdownMenuItem standalone class="text-destructive hover:text-destructive flex items-center">
                                                        <Trash2 class="mr-2 h-4 w-4" />
                                                        Delete
                                                    </DropdownMenuItem>
                                                </Confirm>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>
                <Pagination :data="bills" />
            </div>
        </div>
    </AppLayout>
</template>
