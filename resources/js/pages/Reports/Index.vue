<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Separator } from '@/components/ui/separator';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatCurrency, formatDate } from '@/lib/utils';
import { Head, router } from '@inertiajs/vue3';
import {
    BarChart3,
    Calendar as CalendarIcon,
    CircleDollarSign,
    CreditCard,
    DollarSign,
    FileText,
    TrendingUp,
    Wallet,
} from 'lucide-vue-next';
import { DateValue } from 'reka-ui';
import { computed, ref } from 'vue';

interface Filters {
    start_date: string;
    end_date: string;
}

interface DateRangeStats {
    total_bills_count: number;
    paid_bills_count: number;
    unpaid_bills_count: number;
    total_bills_amount: number;
    paid_amount: number;
    unpaid_amount: number;
    total_expenses: number;
}

interface LifetimeStats {
    total_bills_count: number;
    paid_bills_count: number;
    unpaid_bills_count: number;
    total_bills_amount: number;
    total_paid_amount: number;
    total_unpaid_amount: number;
    total_expenses: number;
}

interface CategoryBreakdown {
    id: number;
    name: string;
    color: string;
    total_bills: number;
    paid_bills: number;
    total_amount: number;
    paid_amount: number;
}

interface PaymentMethodBreakdown {
    method: string;
    count: number;
    total_amount: number;
}

interface MonthlyTrend {
    month: string;
    total_bills: number;
    paid_bills: number;
    bills_amount: number;
    paid_amount: number;
}

interface YearlyComparison {
    year: number;
    total_bills: number;
    paid_bills: number;
    bills_amount: number;
    paid_amount: number;
}

interface Props {
    filters: Filters;
    dateRangeStats: DateRangeStats;
    lifetimeStats: LifetimeStats;
    categoryBreakdown: CategoryBreakdown[];
    paymentMethodBreakdown: PaymentMethodBreakdown[];
    monthlyTrend: MonthlyTrend[];
    yearlyComparison: YearlyComparison[];
}

const props = defineProps<Props>();

const startDate = ref<Date>(new Date(props.filters.start_date));
const endDate = ref<Date>(new Date(props.filters.end_date));

const formattedStartDate = computed((): string => {
    return formatDate(startDate.value);
});

const formattedEndDate = computed((): string => {
    return formatDate(endDate.value);
});

function updateStartDate(date?: DateValue): void {
    const value = date ? date.toString() : new Date().toISOString().split('T')[0];
    startDate.value = new Date(value);
}

function updateEndDate(date?: DateValue): void {
    const value = date ? date.toString() : new Date().toISOString().split('T')[0];
    endDate.value = new Date(value);
}

function applyFilters(): void {
    router.get(
        route('reports.index'),
        {
            start_date: startDate.value.toISOString().split('T')[0],
            end_date: endDate.value.toISOString().split('T')[0],
        },
        {
            preserveState: true,
            replace: true,
        },
    );
}

function setDateRange(range: 'this_month' | 'last_month' | 'this_year' | 'last_year' | 'last_3_months' | 'last_6_months'): void {
    const today = new Date();
    let start = new Date();
    let end = new Date();

    switch (range) {
        case 'this_month':
            start = new Date(today.getFullYear(), today.getMonth(), 1);
            end = new Date(today.getFullYear(), today.getMonth() + 1, 0);
            break;
        case 'last_month':
            start = new Date(today.getFullYear(), today.getMonth() - 1, 1);
            end = new Date(today.getFullYear(), today.getMonth(), 0);
            break;
        case 'last_3_months':
            start = new Date(today.getFullYear(), today.getMonth() - 3, 1);
            end = new Date(today.getFullYear(), today.getMonth() + 1, 0);
            break;
        case 'last_6_months':
            start = new Date(today.getFullYear(), today.getMonth() - 6, 1);
            end = new Date(today.getFullYear(), today.getMonth() + 1, 0);
            break;
        case 'this_year':
            start = new Date(today.getFullYear(), 0, 1);
            end = new Date(today.getFullYear(), 11, 31);
            break;
        case 'last_year':
            start = new Date(today.getFullYear() - 1, 0, 1);
            end = new Date(today.getFullYear() - 1, 11, 31);
            break;
    }

    startDate.value = start;
    endDate.value = end;
    applyFilters();
}

const paymentRate = computed((): number => {
    if (props.dateRangeStats.total_bills_count === 0) return 0;
    return (props.dateRangeStats.paid_bills_count / props.dateRangeStats.total_bills_count) * 100;
});

const lifetimePaymentRate = computed((): number => {
    if (props.lifetimeStats.total_bills_count === 0) return 0;
    return (props.lifetimeStats.paid_bills_count / props.lifetimeStats.total_bills_count) * 100;
});
</script>

<template>
    <AppLayout>
        <Head title="Reports" />

        <div class="p-6 space-y-6">
            <!-- Header -->
            <div>
                <h1 class="text-3xl font-bold tracking-tight">Reports & Analytics</h1>
                <p class="text-muted-foreground">Comprehensive overview of your bills and expenses</p>
            </div>

            <!-- Date Range Filter -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-lg">Date Range Filter</CardTitle>
                    <CardDescription>Select a date range to view your reports</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
                        <div class="flex flex-col gap-2">
                            <label class="text-sm font-medium">Start Date</label>
                            <Popover>
                                <PopoverTrigger as-child>
                                    <Button variant="outline" class="w-[240px] justify-start text-left font-normal">
                                        <CalendarIcon class="mr-2 h-4 w-4" />
                                        {{ formattedStartDate }}
                                    </Button>
                                </PopoverTrigger>
                                <PopoverContent class="w-auto p-0">
                                    <Calendar :model-value="startDate" @update:model-value="updateStartDate" />
                                </PopoverContent>
                            </Popover>
                        </div>

                        <div class="flex flex-col gap-2">
                            <label class="text-sm font-medium">End Date</label>
                            <Popover>
                                <PopoverTrigger as-child>
                                    <Button variant="outline" class="w-[240px] justify-start text-left font-normal">
                                        <CalendarIcon class="mr-2 h-4 w-4" />
                                        {{ formattedEndDate }}
                                    </Button>
                                </PopoverTrigger>
                                <PopoverContent class="w-auto p-0">
                                    <Calendar :model-value="endDate" @update:model-value="updateEndDate" />
                                </PopoverContent>
                            </Popover>
                        </div>

                        <Button @click="applyFilters" class="sm:w-auto w-full">
                            <BarChart3 class="mr-2 h-4 w-4" />
                            Apply Filters
                        </Button>
                    </div>

                    <!-- Quick Date Range Buttons -->
                    <div class="mt-4 flex flex-wrap gap-2">
                        <Button size="sm" variant="secondary" @click="setDateRange('this_month')">This Month</Button>
                        <Button size="sm" variant="secondary" @click="setDateRange('last_month')">Last Month</Button>
                        <Button size="sm" variant="secondary" @click="setDateRange('last_3_months')">Last 3 Months</Button>
                        <Button size="sm" variant="secondary" @click="setDateRange('last_6_months')">Last 6 Months</Button>
                        <Button size="sm" variant="secondary" @click="setDateRange('this_year')">This Year</Button>
                        <Button size="sm" variant="secondary" @click="setDateRange('last_year')">Last Year</Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Tabs for different views -->
            <Tabs default-value="overview" class="space-y-4">
                <TabsList>
                    <TabsTrigger value="overview">Overview</TabsTrigger>
                    <TabsTrigger value="categories">Categories</TabsTrigger>
                    <TabsTrigger value="trends">Trends</TabsTrigger>
                    <TabsTrigger value="lifetime">Lifetime</TabsTrigger>
                </TabsList>

                <!-- Overview Tab -->
                <TabsContent value="overview" class="space-y-4">
                    <!-- Date Range Statistics -->
                    <div>
                        <h2 class="mb-4 text-xl font-semibold">Selected Period Statistics</h2>
                        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                            <Card>
                                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                                    <CardTitle class="text-sm font-medium">Total Bills</CardTitle>
                                    <FileText class="h-4 w-4 text-muted-foreground" />
                                </CardHeader>
                                <CardContent>
                                    <div class="text-2xl font-bold">{{ dateRangeStats.total_bills_count }}</div>
                                    <div class="flex items-center gap-2 text-xs text-muted-foreground">
                                        <Badge variant="outline" class="text-green-600">
                                            {{ dateRangeStats.paid_bills_count }} Paid
                                        </Badge>
                                        <Badge variant="outline" class="text-amber-600">
                                            {{ dateRangeStats.unpaid_bills_count }} Unpaid
                                        </Badge>
                                    </div>
                                </CardContent>
                            </Card>

                            <Card>
                                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                                    <CardTitle class="text-sm font-medium">Total Bills Amount</CardTitle>
                                    <DollarSign class="h-4 w-4 text-muted-foreground" />
                                </CardHeader>
                                <CardContent>
                                    <div class="text-2xl font-bold">{{ formatCurrency(dateRangeStats.total_bills_amount) }}</div>
                                    <p class="text-xs text-muted-foreground">All bills in period</p>
                                </CardContent>
                            </Card>

                            <Card>
                                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                                    <CardTitle class="text-sm font-medium">Total Paid</CardTitle>
                                    <CircleDollarSign class="h-4 w-4 text-green-600" />
                                </CardHeader>
                                <CardContent>
                                    <div class="text-2xl font-bold text-green-600">
                                        {{ formatCurrency(dateRangeStats.paid_amount) }}
                                    </div>
                                    <div class="flex items-center gap-1 text-xs text-muted-foreground">
                                        <TrendingUp class="h-3 w-3" />
                                        {{ paymentRate.toFixed(1) }}% payment rate
                                    </div>
                                </CardContent>
                            </Card>

                            <Card>
                                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                                    <CardTitle class="text-sm font-medium">Remaining Unpaid</CardTitle>
                                    <Wallet class="h-4 w-4 text-amber-600" />
                                </CardHeader>
                                <CardContent>
                                    <div class="text-2xl font-bold text-amber-600">
                                        {{ formatCurrency(dateRangeStats.unpaid_amount) }}
                                    </div>
                                    <p class="text-xs text-muted-foreground">Outstanding amount</p>
                                </CardContent>
                            </Card>
                        </div>
                    </div>

                    <!-- Payment Methods Breakdown -->
                    <Card v-if="paymentMethodBreakdown.length > 0">
                        <CardHeader>
                            <CardTitle>Payment Methods</CardTitle>
                            <CardDescription>Breakdown by payment method for selected period</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Payment Method</TableHead>
                                        <TableHead class="text-right">Transactions</TableHead>
                                        <TableHead class="text-right">Total Amount</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="method in paymentMethodBreakdown" :key="method.method">
                                        <TableCell class="font-medium">
                                            <div class="flex items-center gap-2">
                                                <CreditCard class="h-4 w-4 text-muted-foreground" />
                                                {{ method.method }}
                                            </div>
                                        </TableCell>
                                        <TableCell class="text-right">{{ method.count }}</TableCell>
                                        <TableCell class="text-right font-semibold">
                                            {{ formatCurrency(method.total_amount) }}
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </CardContent>
                    </Card>
                </TabsContent>

                <!-- Categories Tab -->
                <TabsContent value="categories" class="space-y-4">
                    <Card>
                        <CardHeader>
                            <CardTitle>Category Breakdown</CardTitle>
                            <CardDescription>Bills and expenses by category for selected period</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div v-if="categoryBreakdown.length > 0" class="space-y-4">
                                <div
                                    v-for="category in categoryBreakdown"
                                    :key="category.id"
                                    class="flex items-center justify-between rounded-lg border p-4"
                                >
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="h-10 w-10 rounded-full"
                                            :style="{ backgroundColor: category.color || '#6366f1' }"
                                        ></div>
                                        <div>
                                            <h3 class="font-semibold">{{ category.name }}</h3>
                                            <p class="text-sm text-muted-foreground">
                                                {{ category.total_bills }} bills
                                                <span class="ml-2">
                                                    ({{ category.paid_bills }} paid, {{ category.total_bills - category.paid_bills }} unpaid)
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-lg font-bold">{{ formatCurrency(category.total_amount) }}</div>
                                        <div class="text-sm text-green-600">
                                            {{ formatCurrency(category.paid_amount) }} paid
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="py-8 text-center text-muted-foreground">
                                No category data available for the selected period
                            </div>
                        </CardContent>
                    </Card>
                </TabsContent>

                <!-- Trends Tab -->
                <TabsContent value="trends" class="space-y-4">
                    <!-- Monthly Trend -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Monthly Trend (Last 12 Months)</CardTitle>
                            <CardDescription>Your bills and expenses over the past year</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Month</TableHead>
                                        <TableHead class="text-right">Total Bills</TableHead>
                                        <TableHead class="text-right">Paid Bills</TableHead>
                                        <TableHead class="text-right">Bills Amount</TableHead>
                                        <TableHead class="text-right">Paid Amount</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="(month, index) in monthlyTrend" :key="index">
                                        <TableCell class="font-medium">{{ month.month }}</TableCell>
                                        <TableCell class="text-right">{{ month.total_bills }}</TableCell>
                                        <TableCell class="text-right">{{ month.paid_bills }}</TableCell>
                                        <TableCell class="text-right">{{ formatCurrency(month.bills_amount) }}</TableCell>
                                        <TableCell class="text-right text-green-600">
                                            {{ formatCurrency(month.paid_amount) }}
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </CardContent>
                    </Card>

                    <!-- Yearly Comparison -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Yearly Comparison</CardTitle>
                            <CardDescription>Compare your expenses across different years</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Year</TableHead>
                                        <TableHead class="text-right">Total Bills</TableHead>
                                        <TableHead class="text-right">Paid Bills</TableHead>
                                        <TableHead class="text-right">Bills Amount</TableHead>
                                        <TableHead class="text-right">Paid Amount</TableHead>
                                        <TableHead class="text-right">Payment Rate</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="year in yearlyComparison" :key="year.year">
                                        <TableCell class="font-medium">{{ year.year }}</TableCell>
                                        <TableCell class="text-right">{{ year.total_bills }}</TableCell>
                                        <TableCell class="text-right">{{ year.paid_bills }}</TableCell>
                                        <TableCell class="text-right">{{ formatCurrency(year.bills_amount) }}</TableCell>
                                        <TableCell class="text-right text-green-600">
                                            {{ formatCurrency(year.paid_amount) }}
                                        </TableCell>
                                        <TableCell class="text-right">
                                            <Badge variant="outline">
                                                {{ year.total_bills > 0 ? ((year.paid_bills / year.total_bills) * 100).toFixed(1) : 0 }}%
                                            </Badge>
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </CardContent>
                    </Card>
                </TabsContent>

                <!-- Lifetime Tab -->
                <TabsContent value="lifetime" class="space-y-4">
                    <div>
                        <h2 class="mb-4 text-xl font-semibold">All-Time Statistics</h2>
                        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                            <Card>
                                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                                    <CardTitle class="text-sm font-medium">Total Bills</CardTitle>
                                    <FileText class="h-4 w-4 text-muted-foreground" />
                                </CardHeader>
                                <CardContent>
                                    <div class="text-2xl font-bold">{{ lifetimeStats.total_bills_count }}</div>
                                    <div class="flex items-center gap-2 text-xs text-muted-foreground">
                                        <Badge variant="outline" class="text-green-600">
                                            {{ lifetimeStats.paid_bills_count }} Paid
                                        </Badge>
                                        <Badge variant="outline" class="text-amber-600">
                                            {{ lifetimeStats.unpaid_bills_count }} Unpaid
                                        </Badge>
                                    </div>
                                </CardContent>
                            </Card>

                            <Card>
                                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                                    <CardTitle class="text-sm font-medium">Total Bills Amount</CardTitle>
                                    <DollarSign class="h-4 w-4 text-muted-foreground" />
                                </CardHeader>
                                <CardContent>
                                    <div class="text-2xl font-bold">{{ formatCurrency(lifetimeStats.total_bills_amount) }}</div>
                                    <p class="text-xs text-muted-foreground">All bills ever created</p>
                                </CardContent>
                            </Card>

                            <Card>
                                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                                    <CardTitle class="text-sm font-medium">Total Paid (Lifetime)</CardTitle>
                                    <CircleDollarSign class="h-4 w-4 text-green-600" />
                                </CardHeader>
                                <CardContent>
                                    <div class="text-2xl font-bold text-green-600">
                                        {{ formatCurrency(lifetimeStats.total_paid_amount) }}
                                    </div>
                                    <div class="flex items-center gap-1 text-xs text-muted-foreground">
                                        <TrendingUp class="h-3 w-3" />
                                        {{ lifetimePaymentRate.toFixed(1) }}% payment rate
                                    </div>
                                </CardContent>
                            </Card>

                            <Card>
                                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                                    <CardTitle class="text-sm font-medium">Total Unpaid</CardTitle>
                                    <Wallet class="h-4 w-4 text-amber-600" />
                                </CardHeader>
                                <CardContent>
                                    <div class="text-2xl font-bold text-amber-600">
                                        {{ formatCurrency(lifetimeStats.total_unpaid_amount) }}
                                    </div>
                                    <p class="text-xs text-muted-foreground">Outstanding amount</p>
                                </CardContent>
                            </Card>
                        </div>
                    </div>

                    <!-- Lifetime Summary Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Lifetime Summary</CardTitle>
                            <CardDescription>Complete overview of all your bills and payments</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid gap-4 md:grid-cols-2">
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-muted-foreground">Total Bills Created</span>
                                        <span class="font-semibold">{{ lifetimeStats.total_bills_count }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-muted-foreground">Bills Paid</span>
                                        <span class="font-semibold text-green-600">{{ lifetimeStats.paid_bills_count }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-muted-foreground">Bills Unpaid</span>
                                        <span class="font-semibold text-amber-600">{{ lifetimeStats.unpaid_bills_count }}</span>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-muted-foreground">Total Amount</span>
                                        <span class="font-semibold">{{ formatCurrency(lifetimeStats.total_bills_amount) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-muted-foreground">Total Paid</span>
                                        <span class="font-semibold text-green-600">
                                            {{ formatCurrency(lifetimeStats.total_paid_amount) }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-muted-foreground">Total Unpaid</span>
                                        <span class="font-semibold text-amber-600">
                                            {{ formatCurrency(lifetimeStats.total_unpaid_amount) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <Separator />
                            <div class="flex justify-between">
                                <span class="text-sm font-medium">Overall Payment Rate</span>
                                <span class="text-lg font-bold">{{ lifetimePaymentRate.toFixed(1) }}%</span>
                            </div>
                        </CardContent>
                    </Card>
                </TabsContent>
            </Tabs>
        </div>
    </AppLayout>
</template>

