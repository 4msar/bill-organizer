<script setup lang="ts">
import Confirm from '@/components/shared/Confirm.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Table, TableBody, TableCaption, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatCurrency, formatDate } from '@/lib/utils';
import { Bill } from '@/types/model';
import { Head, Link, router } from '@inertiajs/vue3';
import { Calendar, CheckCheck, Clock, DollarSign, Edit, Eye, MoreHorizontal, PlusCircle, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

defineProps<{
    bills: Array<Bill>;
    total_unpaid: number;
    unpaid_count: number;
    upcoming_count: number;
    paid_count: number;
}>();

const activeTab = ref('all');

function markAsPaid(id: string | number) {
    router.patch(route('bills.pay', id));
}
</script>

<template>
    <AppLayout :breadcrumbs="[
        {
            title: 'Bills',
            href: route('bills.index'),
        },
    ]">

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
                            <p class="text-muted-foreground text-xs">{{ unpaid_count }} unpaid bill{{ unpaid_count !== 1
                                ? 's' : '' }}</p>
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
                <div class="mb-6 flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Bills</h2>
                    <Link :href="route('bills.create')">
                    <Button>
                        <PlusCircle class="mr-2 h-4 w-4" />
                        Add New Bill
                    </Button>
                    </Link>
                </div>

                <!-- Bills Tabs and Table -->
                <Card>
                    <CardHeader>
                        <Tabs v-model="activeTab" class="w-full">
                            <TabsList class="grid w-full max-w-md grid-cols-4">
                                <TabsTrigger value="all">All Bills</TabsTrigger>
                                <TabsTrigger value="unpaid">Unpaid</TabsTrigger>
                                <TabsTrigger value="upcoming">Upcoming</TabsTrigger>
                                <TabsTrigger value="paid">Paid</TabsTrigger>
                            </TabsList>
                        </Tabs>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableCaption v-if="bills.length === 0">You don't have any bills yet.</TableCaption>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Title</TableHead>
                                    <TableHead>Category</TableHead>
                                    <TableHead>Amount</TableHead>
                                    <TableHead>Due Date</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead class="text-right">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="bill in bills.filter((bill) => {
                                    if (activeTab === 'upcoming')
                                        return new Date(bill.due_date as string) > new Date() && bill.status === 'unpaid';
                                    if (activeTab === 'all') return true;
                                    if (activeTab === 'unpaid') return bill.status === 'unpaid';
                                    if (activeTab === 'paid') return bill.status === 'paid';
                                })" :key="bill.id" @click.stop="router.visit(route('bills.show', bill.id))"
                                    class="hover:bg-muted/50 cursor-pointer">
                                    <TableCell class="font-medium">
                                        {{ bill.title }}
                                        <span v-if="bill.is_recurring" class="ml-2">
                                            <Badge variant="outline">Recurring</Badge>
                                        </span>
                                    </TableCell>
                                    <TableCell>{{ bill.category?.name || 'Uncategorized' }}</TableCell>
                                    <TableCell>{{ formatCurrency(bill.amount, $page.props?.team?.current?.currency as
                                        string) }}</TableCell>
                                    <TableCell>
                                        <span :class="{
                                            'text-destructive': new Date(bill.due_date as string) < new Date() && bill.status === 'unpaid',
                                        }">
                                            {{ formatDate(bill.due_date as string) }}
                                        </span>
                                    </TableCell>
                                    <TableCell>
                                        <Badge :variant="bill.status === 'paid' ? 'secondary' : 'default'">
                                            {{ bill.status === 'paid' ? 'Paid' : 'Unpaid' }}
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
                                                    <Link :href="route('bills.show', bill.id)"
                                                        class="flex items-center gap-2">
                                                    <Eye class="mr-2 h-4 w-4" />
                                                    View details
                                                    </Link>
                                                </DropdownMenuItem>
                                                <DropdownMenuItem class="flex items-center">
                                                    <Link :href="route('bills.edit', bill.id)"
                                                        class="flex items-center gap-2">
                                                    <Edit class="mr-2 h-4 w-4" />
                                                    Edit
                                                    </Link>
                                                </DropdownMenuItem>
                                                <DropdownMenuItem v-if="bill.status === 'unpaid'"
                                                    @click.stop="markAsPaid(bill.id)" class="flex items-center">
                                                    <CheckCheck class="mr-2 h-4 w-4" />
                                                    Mark as paid
                                                </DropdownMenuItem>
                                                <Confirm :modal="true" title="Are you sure?"
                                                    :url="route('bills.destroy', bill.id)">
                                                    <DropdownMenuItem standalone
                                                        class="text-destructive hover:text-destructive flex items-center">
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
            </div>
        </div>
    </AppLayout>
</template>
