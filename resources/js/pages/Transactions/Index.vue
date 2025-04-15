<script setup lang="ts">
import TransactionList from '@/components/transactions/TransactionList.vue';
import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatDate } from '@/lib/utils';
import { Head, router } from '@inertiajs/vue3';
import { CalendarIcon, Filter, RefreshCw } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Bill {
    id: number;
    title: string;
}

interface Transaction {
    id: number;
    bill_id: number;
    amount: number;
    payment_date: string;
    payment_method: string | null;
    attachment: string | null;
    notes: string | null;
    created_at: string;
    bill?: Bill;
}

interface PaginationLinks {
    first: string | null;
    last: string | null;
    prev: string | null;
    next: string | null;
}

interface PaginationMeta {
    current_page: number;
    from: number;
    last_page: number;
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
    path: string;
    per_page: number;
    to: number;
    total: number;
}

interface Transactions {
    data: Transaction[];
    links: PaginationLinks;
    meta: PaginationMeta;
}

interface Filters {
    bill_id?: string | null;
    date_from?: string | null;
    date_to?: string | null;
    payment_method?: string | null;
}

interface Props {
    transactions: Transactions;
    filters: Filters;
    bills?: Bill[];
}

const props = defineProps<Props>();

const hasTransactions = computed(() => {
    return props.transactions.data.length > 0;
});

const filters = ref<Filters>({
    bill_id: props.filters.bill_id || null,
    date_from: props.filters.date_from || null,
    date_to: props.filters.date_to || null,
    payment_method: props.filters.payment_method || null,
});

const dateFrom = ref<Date | null>(filters.value.date_from ? new Date(filters.value.date_from) : null);
const dateTo = ref<Date | null>(filters.value.date_to ? new Date(filters.value.date_to) : null);

const formattedDateFrom = computed((): string => {
    if (!dateFrom.value) return 'Select start date';
    return formatDate(dateFrom.value);
});

const formattedDateTo = computed((): string => {
    if (!dateTo.value) return 'Select end date';
    return formatDate(dateTo.value);
});

function updateDateFrom(date: Date): void {
    dateFrom.value = date;
    filters.value.date_from = date.toISOString().split('T')[0];
}

function updateDateTo(date: Date): void {
    dateTo.value = date;
    filters.value.date_to = date.toISOString().split('T')[0];
}

function applyFilters(): void {
    router.get(
        route('transactions.index'),
        {
            ...filters.value,
            page: 1,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
}

function resetFilters(): void {
    filters.value = {
        bill_id: null,
        date_from: null,
        date_to: null,
        payment_method: null,
    };
    dateFrom.value = null;
    dateTo.value = null;
    router.get(
        route('transactions.index'),
        {},
        {
            preserveState: true,
            replace: true,
        },
    );
}

const paymentMethods = {
    cash: 'Cash',
    credit_card: 'Credit Card',
    debit_card: 'Debit Card',
    bank_transfer: 'Bank Transfer',
    paypal: 'PayPal',
    crypto: 'Cryptocurrency',
    check: 'Check',
    other: 'Other',
};
</script>

<template>
    <AppLayout
        :breadcrumbs="[
            {
                title: 'Transactions',
                href: route('transactions.index'),
            },
        ]"
    >
        <Head title="Transactions" />

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <h1 class="text-2xl font-bold">Transaction History</h1>
                    <p class="text-muted-foreground">View and manage all your bill payments</p>
                </div>

                <!-- Filters -->
                <Card class="mb-6">
                    <CardHeader>
                        <CardTitle class="flex items-center text-lg">
                            <Filter class="mr-2 h-5 w-5" />
                            Filter Transactions
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                            <!-- Bill Filter -->
                            <div class="space-y-2">
                                <label class="text-sm font-medium">Bill</label>
                                <Select v-model="filters.bill_id">
                                    <SelectTrigger>
                                        <SelectValue placeholder="All Bills" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem :value="null">All Bills</SelectItem>
                                        <SelectItem v-for="bill in bills" :key="bill.id" :value="bill.id.toString()">
                                            {{ bill.title }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <!-- Date From -->
                            <div class="space-y-2">
                                <label class="text-sm font-medium">From Date</label>
                                <Popover>
                                    <PopoverTrigger asChild>
                                        <Button
                                            variant="outline"
                                            class="w-full justify-start text-left font-normal"
                                            :class="!dateFrom ? 'text-muted-foreground' : ''"
                                        >
                                            <CalendarIcon class="mr-2 h-4 w-4" />
                                            {{ formattedDateFrom }}
                                        </Button>
                                    </PopoverTrigger>
                                    <PopoverContent class="w-auto p-0">
                                        <Calendar :selected-date="dateFrom" @update:selected-date="updateDateFrom" />
                                    </PopoverContent>
                                </Popover>
                            </div>

                            <!-- Date To -->
                            <div class="space-y-2">
                                <label class="text-sm font-medium">To Date</label>
                                <Popover>
                                    <PopoverTrigger asChild>
                                        <Button
                                            variant="outline"
                                            class="w-full justify-start text-left font-normal"
                                            :class="!dateTo ? 'text-muted-foreground' : ''"
                                        >
                                            <CalendarIcon class="mr-2 h-4 w-4" />
                                            {{ formattedDateTo }}
                                        </Button>
                                    </PopoverTrigger>
                                    <PopoverContent class="w-auto p-0">
                                        <Calendar :selected-date="dateTo" @update:selected-date="updateDateTo" />
                                    </PopoverContent>
                                </Popover>
                            </div>

                            <!-- Payment Method -->
                            <div class="space-y-2">
                                <label class="text-sm font-medium">Payment Method</label>
                                <Select v-model="filters.payment_method">
                                    <SelectTrigger>
                                        <SelectValue placeholder="All Methods" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem :value="null">All Methods</SelectItem>
                                        <SelectItem v-for="(label, value) in paymentMethods" :key="value" :value="value">
                                            {{ label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <div class="mt-4 flex justify-end gap-2">
                            <Button variant="outline" @click="resetFilters">
                                <RefreshCw class="mr-2 h-4 w-4" />
                                Reset
                            </Button>
                            <Button @click="applyFilters">
                                <Filter class="mr-2 h-4 w-4" />
                                Apply Filters
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <!-- Transactions List -->
                <TransactionList :transactions="transactions.data" :showBillLink="true" />

                <!-- Pagination -->
                <div v-if="hasTransactions && transactions?.meta?.last_page > 1" class="mt-6 flex justify-center">
                    <div class="flex items-center space-x-2">
                        <Button variant="outline" size="sm" :disabled="!transactions.links.prev" @click="router.get(transactions?.links?.prev)">
                            Previous
                        </Button>

                        <span class="text-muted-foreground text-sm">
                            Page {{ transactions.meta.current_page }} of {{ transactions.meta.last_page }}
                        </span>

                        <Button variant="outline" size="sm" :disabled="!transactions.links.next" @click="router.get(transactions?.links?.next)">
                            Next
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
