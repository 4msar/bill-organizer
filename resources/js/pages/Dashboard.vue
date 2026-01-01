<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatCurrency, formatDate } from '@/lib/utils';
import { SharedData } from '@/types';
import { Bill, Category } from '@/types/model';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRightCircle, CalendarDays, CreditCard, DollarSign, Landmark, Plus, Wallet } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface ChartData {
    months: string[];
    series: {
        category: string;
        data: number[];
    }[];
}

interface Stats {
    totalBills: number;
    paidBills: number;
    unpaidBills: number;
    upcomingBills: number;
    totalPaidAmount: number;
    totalUnpaidAmount: number;
    amountDueThisMonth: number;
}

interface Props {
    stats: Stats;
    categories: (Category & {
        total_bills_count: number;
        unpaid_bills_count: number;
        total_amount: number;
        unpaid_amount: number;
        paid_bills_count: number;
    })[];
    recentBills: Bill[];
    upcomingBills: Bill[];
    chartData: ChartData;
    currentMonthStats: {
        dueBills: Bill[];
        paidBills: Bill[];
    };
}

const { stats, categories, recentBills, upcomingBills, ...props } = defineProps<Props & SharedData>();

// Get current date for greeting
const currentHour = new Date().getHours();
const greeting = computed((): string => {
    if (currentHour < 12) return 'Good morning';
    if (currentHour < 18) return 'Good afternoon';
    return 'Good evening';
});

// Date formatting for due dates
const getDueStatus = (dueDate: string): string => {
    const today = new Date();
    const due = new Date(dueDate);
    const diffTime = due.getTime() - today.getTime();
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

    if (diffDays < 0) return 'overdue';
    if (diffDays === 0) return 'today';
    if (diffDays === 1) return 'tomorrow';
    if (diffDays < 7) return `in ${diffDays} days`;
    return formatDate(dueDate);
};

// Chart setup
const chartOptions = ref({
    chart: {
        type: 'bar',
        stacked: true,
        toolbar: {
            show: false,
        },
    },
    plotOptions: {
        bar: {
            horizontal: false,
        },
    },
    stroke: {
        width: 1,
        colors: ['#fff'],
    },
    xaxis: {
        categories: props.chartData.months,
    },
    legend: {
        position: 'top',
    },
    fill: {
        opacity: 1,
    },
});

const chartSeries = computed(() => {
    return props.chartData.series.map((item) => ({
        name: item.category,
        data: item.data,
    }));
});

// Current month stats chart
const currentMonthChartOptions = ref({
    chart: {
        type: 'line',
        toolbar: {
            show: false,
        },
    },
    stroke: {
        curve: 'smooth',
        width: 3,
    },
    xaxis: {
        type: 'datetime',
    },
    yaxis: {
        labels: {
            formatter: (value: number) => formatCurrency(value, props.team?.current?.currency as string),
        },
    },
    legend: {
        position: 'top',
    },
    tooltip: {
        x: {
            format: 'dd MMM',
        },
    },
    colors: ['#ef4444', '#22c55e'],
});

const currentMonthChartSeries = computed(() => {
    // Group due bills by date
    const dueBillsByDate: Record<string, number> = {};
    props.currentMonthStats.dueBills.forEach((bill) => {
        const date = new Date(bill.due_date as string).toISOString().split('T')[0];
        dueBillsByDate[date] = (dueBillsByDate[date] || 0) + parseFloat(bill.amount.toString());
    });

    // Group paid bills by date
    const paidBillsByDate: Record<string, number> = {};
    props.currentMonthStats.paidBills.forEach((bill) => {
        const date = new Date(bill.due_date as string).toISOString().split('T')[0];
        paidBillsByDate[date] = (paidBillsByDate[date] || 0) + parseFloat(bill.amount.toString());
    });

    // Get all unique dates and sort them
    const allDates = Array.from(new Set([...Object.keys(dueBillsByDate), ...Object.keys(paidBillsByDate)])).sort();

    // Create series data
    const dueData = allDates.map((date) => ({
        x: new Date(date).getTime(),
        y: dueBillsByDate[date] || 0,
    }));

    const paidData = allDates.map((date) => ({
        x: new Date(date).getTime(),
        y: paidBillsByDate[date] || 0,
    }));

    return [
        {
            name: 'Due Bills',
            data: dueData,
        },
        {
            name: 'Paid Bills',
            data: paidData,
        },
    ];
});

// Calculate payment progress
const paymentProgress = computed((): number => {
    const totalBills = stats.totalBills;
    return totalBills > 0 ? Math.round((stats.paidBills / totalBills) * 100) : 0;
});

// Get user from session for greeting
const userName = props.auth.user.name || 'User';
</script>

<template>
    <AppLayout
        :breadcrumbs="[
            {
                title: 'Dashboard',
                href: route('dashboard'),
            },
        ]"
    >
        <Head title="Dashboard" />

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Welcome Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold tracking-tight">{{ greeting }}, {{ userName }}</h1>
                    <p class="text-muted-foreground">Here's an overview of your bills for current month and year.</p>
                </div>

                <!-- Stats Cards -->
                <div class="mb-8 grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <!-- Due This Month -->
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium">Due This Month</CardTitle>
                            <CalendarDays class="text-muted-foreground h-4 w-4" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">
                                {{ formatCurrency(stats.amountDueThisMonth, $page.props?.team?.current?.currency as string) }}
                            </div>
                            <div class="text-muted-foreground mt-1 text-xs">
                                {{ new Date().toLocaleDateString('en-US', { month: 'long', year: 'numeric' }) }}
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Total Bills -->
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium">
                                Total Bills
                                <small class="text-muted-foreground"> (this year) </small>
                            </CardTitle>
                            <CreditCard class="text-muted-foreground h-4 w-4" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">{{ stats.totalBills }}</div>
                            <div class="text-muted-foreground mt-1 text-xs">{{ stats.paidBills }} paid, {{ stats.unpaidBills }} unpaid</div>
                            <div class="bg-muted mt-3 h-2 w-full overflow-hidden rounded-full">
                                <div class="bg-primary h-full" :style="{ width: `${paymentProgress}%` }" />
                            </div>
                            <div class="text-muted-foreground mt-1 text-xs">{{ paymentProgress }}% paid</div>
                        </CardContent>
                    </Card>

                    <!-- Total Due -->
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium">
                                Total Due
                                <small class="text-muted-foreground"> (this year) </small>
                            </CardTitle>
                            <DollarSign class="text-muted-foreground h-4 w-4" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-destructive text-2xl font-bold">
                                {{ formatCurrency(stats.totalUnpaidAmount, $page.props?.team?.current?.currency as string) }}
                            </div>
                            <div class="text-muted-foreground mt-1 text-xs">{{ stats.unpaidBills }} unpaid bills</div>
                        </CardContent>
                    </Card>

                    <!-- Paid Amount -->
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium">
                                Total Paid
                                <small class="text-muted-foreground"> (this year) </small>
                            </CardTitle>
                            <Wallet class="text-muted-foreground h-4 w-4" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-success text-2xl font-bold">
                                {{ formatCurrency(stats.totalPaidAmount, $page.props?.team?.current?.currency as string) }}
                            </div>
                            <div class="text-muted-foreground mt-1 text-xs">{{ stats.paidBills }} paid bills</div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Bills and Categories Tabs -->
                <Tabs defaultValue="upcoming" class="mb-8">
                    <TabsList class="grid w-full grid-cols-3">
                        <TabsTrigger value="upcoming">Upcoming Bills</TabsTrigger>
                        <TabsTrigger value="recent">Recent Bills</TabsTrigger>
                        <TabsTrigger value="categories">Categorized Bills</TabsTrigger>
                    </TabsList>

                    <!-- Upcoming Bills Tab -->
                    <TabsContent value="upcoming">
                        <Card>
                            <CardHeader>
                                <CardTitle>Upcoming Bills</CardTitle>
                                <CardDescription> Bills that are due soon </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>Title</TableHead>
                                            <TableHead>Amount</TableHead>
                                            <TableHead>Due Date</TableHead>
                                            <TableHead>Category</TableHead>
                                            <TableHead class="text-right">Actions</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        <TableRow v-for="bill in upcomingBills" :key="bill.id">
                                            <TableCell class="font-medium">
                                                {{ bill.title }}
                                                <Badge v-if="bill.is_recurring" variant="secondary" class="mr-2"> Recurring </Badge>
                                                <Badge
                                                    v-if="bill.has_trial"
                                                    variant="outline"
                                                    class="border-blue-200 bg-blue-50 text-xs text-blue-700"
                                                    >Trial
                                                </Badge>
                                            </TableCell>
                                            <TableCell>{{ formatCurrency(bill.amount, $page.props?.team?.current?.currency as string) }}</TableCell>
                                            <TableCell>
                                                <span
                                                    :class="{
                                                        'text-destructive': getDueStatus(bill.due_date as string) === 'overdue',
                                                        'font-medium':
                                                            getDueStatus(bill.due_date as string) === 'today' ||
                                                            getDueStatus(bill.due_date as string) === 'tomorrow',
                                                    }"
                                                >
                                                    {{ getDueStatus(bill.due_date as string) }}
                                                </span>
                                            </TableCell>
                                            <TableCell>{{ bill.category?.name || 'Uncategorized' }}</TableCell>
                                            <TableCell class="text-right">
                                                <Button variant="ghost" size="sm" asChild>
                                                    <Link :href="route('bills.show', bill.id)">
                                                        <ArrowRightCircle class="h-4 w-4" />
                                                        <span class="sr-only">View</span>
                                                    </Link>
                                                </Button>
                                            </TableCell>
                                        </TableRow>
                                        <TableRow v-if="upcomingBills.length === 0">
                                            <TableCell colspan="5" class="text-muted-foreground py-4 text-center"> No upcoming bills </TableCell>
                                        </TableRow>
                                    </TableBody>
                                </Table>
                            </CardContent>
                            <CardFooter>
                                <Button variant="outline" asChild class="w-full">
                                    <Link :href="route('bills.index')"> View All Bills </Link>
                                </Button>
                            </CardFooter>
                        </Card>
                    </TabsContent>

                    <!-- Recent Bills Tab -->
                    <TabsContent value="recent">
                        <Card>
                            <CardHeader>
                                <CardTitle>Recent Bills</CardTitle>
                                <CardDescription> Your most recently added bills </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>Title</TableHead>
                                            <TableHead>Amount</TableHead>
                                            <TableHead>Status</TableHead>
                                            <TableHead>Category</TableHead>
                                            <TableHead class="text-right">Actions</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        <TableRow v-for="bill in recentBills" :key="bill.id">
                                            <TableCell class="font-medium">{{ bill.title }}</TableCell>
                                            <TableCell>{{ formatCurrency(bill.amount, $page.props?.team?.current?.currency as string) }}</TableCell>
                                            <TableCell>
                                                <Badge :variant="bill.status === 'paid' ? 'secondary' : 'default'">
                                                    {{ bill.status === 'paid' ? 'Paid' : 'Unpaid' }}
                                                </Badge>
                                            </TableCell>
                                            <TableCell>{{ bill.category?.name || 'Uncategorized' }}</TableCell>
                                            <TableCell class="text-right">
                                                <Button variant="ghost" size="sm" asChild>
                                                    <Link :href="route('bills.show', bill.id)">
                                                        <ArrowRightCircle class="h-4 w-4" />
                                                        <span class="sr-only">View</span>
                                                    </Link>
                                                </Button>
                                            </TableCell>
                                        </TableRow>
                                        <TableRow v-if="recentBills.length === 0">
                                            <TableCell colspan="5" class="text-muted-foreground py-4 text-center"> No bills yet </TableCell>
                                        </TableRow>
                                    </TableBody>
                                </Table>
                            </CardContent>
                            <CardFooter>
                                <Button variant="outline" asChild class="w-full">
                                    <Link :href="route('bills.create')">
                                        <Plus class="mr-2 h-4 w-4" />
                                        Add New Bill
                                    </Link>
                                </Button>
                            </CardFooter>
                        </Card>
                    </TabsContent>
                    <!-- Categorized Bills Tab -->
                    <TabsContent value="categories">
                        <Card>
                            <CardHeader>
                                <CardTitle>Categories</CardTitle>
                                <CardDescription> Overview of your spending categories </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>Category</TableHead>
                                            <TableHead>Bills</TableHead>
                                            <TableHead class="text-right">Amount Due</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        <TableRow v-for="category in categories" :key="category.id">
                                            <TableCell class="font-medium">{{ category.name }}</TableCell>
                                            <TableCell>
                                                {{ category.paid_bills_count + category.unpaid_bills_count }}
                                                <span class="text-muted-foreground"> ({{ category.unpaid_bills_count }} unpaid) </span>
                                            </TableCell>
                                            <TableCell class="text-right">
                                                {{ formatCurrency(category.total_amount, $page.props?.team?.current?.currency as string) }}
                                            </TableCell>
                                        </TableRow>
                                        <TableRow v-if="categories.length === 0">
                                            <TableCell colspan="3" class="text-muted-foreground py-4 text-center"> No categories yet </TableCell>
                                        </TableRow>
                                    </TableBody>
                                </Table>
                            </CardContent>
                            <CardFooter>
                                <Button variant="outline" asChild class="w-full">
                                    <Link :href="route('categories.index')"> Manage Categories </Link>
                                </Button>
                            </CardFooter>
                        </Card>
                    </TabsContent>
                </Tabs>

                <!-- Charts Overview -->
                <div class="mb-8 grid gap-4 md:grid-cols-2">
                    <!-- Unpaid and paid Bills chart for current months -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Current month stats</CardTitle>
                            <CardDescription>Current month paid and unpaid bills by date</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <!-- For ApexCharts, we need to add the library to the project -->
                            <div class="flex h-[300px] w-full items-center justify-center">
                                <div
                                    v-if="
                                        currentMonthChartSeries.length > 0 &&
                                        (currentMonthStats.dueBills.length > 0 || currentMonthStats.paidBills.length > 0)
                                    "
                                    class="h-full w-full"
                                >
                                    <apexchart type="line" height="300" :options="currentMonthChartOptions" :series="currentMonthChartSeries">
                                    </apexchart>
                                </div>
                                <div v-else class="text-muted-foreground text-center">Not enough data to display chart</div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Spending Chart -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Monthly Spending</CardTitle>
                            <CardDescription> Your spending trends over the last 6 months </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <!-- For ApexCharts, we need to add the library to the project -->
                            <div class="flex h-[300px] w-full items-center justify-center">
                                <div v-if="currentMonthStats.dueBills.length > 0 || currentMonthStats.paidBills.length > 0" class="h-full w-full">
                                    <!-- This is a placeholder for ApexCharts -->
                                    <!-- In a real implementation, you would use: -->
                                    <apexchart type="bar" height="300" :options="chartOptions" :series="chartSeries"> </apexchart>
                                </div>
                                <div v-else class="text-muted-foreground text-center">Not enough data to display chart</div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Quick Actions -->
                <div class="flex flex-wrap gap-4">
                    <Button asChild>
                        <Link :href="route('bills.create')">
                            <Plus class="mr-2 h-4 w-4" />
                            Add New Bill
                        </Link>
                    </Button>
                    <Button variant="outline" asChild>
                        <Link :href="route('bills.index')">
                            <CreditCard class="mr-2 h-4 w-4" />
                            View All Bills
                        </Link>
                    </Button>
                    <Button variant="outline" asChild>
                        <Link :href="route('categories.index', { action: 'create' })">
                            <Landmark class="mr-2 h-4 w-4" />
                            Add Category
                        </Link>
                    </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
