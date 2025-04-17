<script setup lang="ts">
import PaymentForm from '@/components/bills/PaymentForm.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatDate } from '@/lib/utils';
import { Bill } from '@/types/model';
import { Head, Link, router } from '@inertiajs/vue3';
import { AlertCircle, ArrowLeft, Receipt } from 'lucide-vue-next';
import { computed } from 'vue';

interface Props {
    bill: Bill;
    paymentMethods: Record<string, string>;
}

const props = defineProps<Props>();

// Calculate next due date
const nextDueDate = computed(() => {
    if (!props.bill.is_recurring || !props.bill.recurrence_period) return null;

    const currentDueDate = new Date(props.bill.due_date);

    switch (props.bill.recurrence_period) {
        case 'weekly':
            const weeklyDate = new Date(currentDueDate);
            weeklyDate.setDate(weeklyDate.getDate() + 7);
            return weeklyDate.toISOString().split('T')[0];
        case 'monthly':
            const monthlyDate = new Date(currentDueDate);
            monthlyDate.setMonth(monthlyDate.getMonth() + 1);
            return monthlyDate.toISOString().split('T')[0];
        case 'yearly':
            const yearlyDate = new Date(currentDueDate);
            yearlyDate.setFullYear(yearlyDate.getFullYear() + 1);
            return yearlyDate.toISOString().split('T')[0];
        default:
            return null;
    }
});

// Check if bill is past due
const isPastDue = computed((): boolean => {
    return new Date(props.bill.due_date) < new Date();
});

function onFormSuccess(): void {
    router.visit(route('bills.show', props.bill.id));
}

function onFormCancel(): void {
    router.visit(route('bills.show', props.bill.id));
}
</script>

<template>
    <AppLayout
        :breadcrumbs="[
            {
                title: 'Bills',
                href: route('bills.index'),
            },
            {
                title: `Pay Bill (${bill.title})`,
                href: route('bills.pay', bill.id),
            },
        ]"
    >
        <Head :title="`Pay Bill - ${bill.title}`" />

        <div class="py-6">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <!-- Back button and header -->
                <div class="mb-6">
                    <Link :href="route('bills.show', bill.id)" class="text-muted-foreground flex items-center text-sm hover:underline">
                        <ArrowLeft class="mr-1 h-4 w-4" />
                        Back to Bill
                    </Link>
                    <h1 class="mt-2 flex items-center text-2xl font-bold">
                        <Receipt class="mr-2 h-6 w-6" />
                        Record Payment
                    </h1>
                    <p class="text-muted-foreground">Record payment details for {{ bill.title }}</p>
                </div>

                <!-- Past Due Alert -->
                <Alert v-if="isPastDue" variant="destructive" class="mb-6">
                    <AlertCircle class="h-4 w-4" />
                    <AlertTitle>Past Due!</AlertTitle>
                    <AlertDescription> This bill was due on {{ formatDate(bill.due_date) }} and is still unpaid. </AlertDescription>
                </Alert>

                <!-- Payment Card -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center justify-between">
                            <span>{{ bill.title }}</span>
                            <Badge :variant="bill.status === 'paid' ? 'secondary' : 'default'">
                                {{ bill.status === 'paid' ? 'Paid' : 'Unpaid' }}
                            </Badge>
                        </CardTitle>
                        <CardDescription v-if="bill.category"> Category: {{ bill.category.name }} </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <!-- Using the separated PaymentForm component -->
                        <PaymentForm
                            :bill="bill"
                            :payment-methods="paymentMethods"
                            :next-due-date="nextDueDate"
                            :show-cancel-button="true"
                            @cancel="onFormCancel"
                            @success="onFormSuccess"
                        />
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
