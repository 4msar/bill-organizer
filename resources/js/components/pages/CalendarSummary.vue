<script setup lang="ts">
import { formatCurrency } from '@/lib/utils';
import { Bill } from '@/types';
import { computed } from 'vue';

const props = defineProps<{
    bills: Bill[];
}>();

// Calculate summary statistics
const summary = computed(() => {
    const bills = props.bills;

    const totalAmount = bills.reduce((sum, bill) => sum + Number(bill.amount || 0), 0);
    const paidAmount = bills.filter((b) => b.status === 'paid').reduce((sum, b) => sum + Number(b.amount || 0), 0);
    const unpaidAmount = bills.filter((b) => b.status === 'unpaid').reduce((sum, b) => sum + Number(b.amount || 0), 0);
    const overdueAmount = bills.filter((b) => b.status === 'overdue').reduce((sum, b) => sum + Number(b.amount || 0), 0);

    const counts = {
        total: bills.length,
        paid: bills.filter((b) => b.status === 'paid').length,
        unpaid: bills.filter((b) => b.status === 'unpaid').length,
        overdue: bills.filter((b) => b.status === 'overdue').length,
    };

    // Group by category
    const byCategory = bills.reduce(
        (acc, bill) => {
            const categoryName = bill.category?.name || 'Uncategorized';
            if (!acc[categoryName]) {
                acc[categoryName] = { count: 0, amount: 0 };
            }
            acc[categoryName].count++;
            acc[categoryName].amount += Number(bill.amount || 0);
            return acc;
        },
        {} as Record<string, { count: number; amount: number }>,
    );

    return {
        totalAmount,
        paidAmount,
        unpaidAmount,
        overdueAmount,
        counts,
        byCategory,
    };
});
</script>

<template>
    <div class="space-y-6">
        <!-- Main Summary Card -->
        <div class="bg-card rounded-lg border p-6 shadow-sm">
            <div class="mb-6">
                <h2 class="text-xl font-semibold">Summary</h2>
                <p class="text-muted-foreground text-sm">A quick overview of your selected month's bills</p>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                <!-- Total Bills -->
                <div class="space-y-2">
                    <p class="text-muted-foreground text-xs font-medium tracking-wider uppercase">Total Bills</p>
                    <p class="text-2xl font-bold">{{ summary.counts.total }}</p>
                    <p class="text-muted-foreground text-sm">{{ formatCurrency(summary.totalAmount, $page.props.team.current.currency) }}</p>
                </div>

                <!-- Paid -->
                <div class="space-y-2">
                    <p class="text-muted-foreground text-xs font-medium tracking-wider uppercase">Paid</p>
                    <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ summary.counts.paid }}</p>
                    <p class="text-sm text-emerald-600/80 dark:text-emerald-400/80">
                        {{ formatCurrency(summary.paidAmount, $page.props.team.current.currency) }}
                    </p>
                </div>

                <!-- Unpaid -->
                <div class="space-y-2">
                    <p class="text-muted-foreground text-xs font-medium tracking-wider uppercase">Unpaid</p>
                    <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ summary.counts.unpaid }}</p>
                    <p class="text-sm text-amber-600/80 dark:text-amber-400/80">
                        {{ formatCurrency(summary.unpaidAmount, $page.props.team.current.currency) }}
                    </p>
                </div>

                <!-- Overdue -->
                <div class="space-y-2">
                    <p class="text-muted-foreground text-xs font-medium tracking-wider uppercase">Overdue</p>
                    <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ summary.counts.overdue }}</p>
                    <p class="text-sm text-red-600/80 dark:text-red-400/80">
                        {{ formatCurrency(summary.overdueAmount, $page.props.team.current.currency) }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Category Breakdown -->
        <div class="bg-card rounded-lg border p-6 shadow-sm" v-if="Object.keys(summary.byCategory).length > 0">
            <h3 class="mb-4 text-lg font-semibold">By Category</h3>
            <div class="space-y-3">
                <div
                    v-for="(data, category) in summary.byCategory"
                    :key="category"
                    class="border-border/50 bg-muted/30 hover:bg-muted/50 flex items-center justify-between rounded-lg border p-4 transition-colors"
                >
                    <div>
                        <p class="font-medium">{{ category }}</p>
                        <p class="text-muted-foreground text-sm">{{ data.count }} {{ data.count === 1 ? 'bill' : 'bills' }}</p>
                    </div>
                    <p class="font-semibold">{{ formatCurrency(data.amount, $page.props.team.current.currency) }}</p>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-if="summary.counts.total === 0" class="bg-card rounded-lg border border-dashed p-8 text-center">
            <p class="text-muted-foreground">No bills for this month</p>
        </div>
    </div>
</template>
