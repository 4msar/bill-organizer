<script setup lang="ts">
import PaymentDialog from '@/components/bills/PaymentDialog.vue';
import TransactionList from '@/components/transactions/TransactionList.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatCurrency, formatDate } from '@/lib/utils';
import { Head, Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import { CalendarIcon, Clock, Edit, Receipt, RotateCcw, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Category {
    id: number;
    name: string;
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
}

interface Bill {
    id: number;
    title: string;
    description: string | null;
    amount: number;
    due_date: string;
    status: 'paid' | 'unpaid';
    is_recurring: boolean;
    recurrence_period: 'weekly' | 'monthly' | 'yearly' | null;
    category_id: number | null;
    category?: Category;
    created_at: string;
    updated_at: string;
    transactions?: Transaction[];
}

interface Props {
    bill: Bill;
}

const props = defineProps<Props>();

const bill = ref<Bill>(props.bill);
const isPaymentDialogOpen = ref(false);
const paymentMethods = ref<Record<string, string>>({});
const nextDueDate = ref<string | null>(null);
const isLoading = ref(false);

const isPastDue = computed((): boolean => {
    return new Date(bill.value.due_date) < new Date() && bill.value.status === 'unpaid';
});

const hasTransactions = computed((): boolean => {
    return bill.value.transactions && bill.value.transactions.length > 0;
});

function deleteBill(): void {
    if (confirm('Are you sure you want to delete this bill?')) {
        router.delete(route('bills.destroy', bill.value.id));
    }
}

async function openPaymentDialog(): Promise<void> {
    isLoading.value = true;
    try {
        const response = await axios.get(route('bills.payment-details', bill.value.id));
        paymentMethods.value = response.data.paymentMethods;
        nextDueDate.value = response.data.nextDueDate;
        isPaymentDialogOpen.value = true;
    } catch (error) {
        console.error('Error fetching payment details:', error);
    } finally {
        isLoading.value = false;
    }
}

function onPaymentComplete(): void {
    // Refresh the page to show updated bill status
    router.reload();
}
</script>

<template>
    <AppLayout>
        <Head :title="bill.title" />

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Back Button and Title -->
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <Link :href="route('bills.index')" class="text-muted-foreground text-sm hover:underline"> &larr; Back to Bills </Link>
                        <h2 class="mt-1 text-xl font-semibold text-gray-800 dark:text-gray-200">
                            {{ bill.title }}
                        </h2>
                    </div>
                    <div class="flex gap-2">
                        <Button variant="outline" asChild>
                            <Link :href="route('bills.edit', bill.id)">
                                <Edit class="mr-2 h-4 w-4" />
                                Edit
                            </Link>
                        </Button>
                        <Button variant="destructive" @click="deleteBill">
                            <Trash2 class="mr-2 h-4 w-4" />
                            Delete
                        </Button>
                    </div>
                </div>

                <!-- Past Due Alert -->
                <Alert v-if="isPastDue" variant="destructive" class="mb-6">
                    <AlertTitle>Past Due!</AlertTitle>
                    <AlertDescription> This bill was due on {{ formatDate(bill.due_date) }} and is still unpaid. </AlertDescription>
                </Alert>

                <!-- Bill Details Card -->
                <Card>
                    <CardHeader>
                        <div class="flex items-start justify-between">
                            <div>
                                <CardTitle class="flex items-center gap-2">
                                    {{ bill.title }}
                                    <Badge v-if="bill.is_recurring" variant="outline">Recurring</Badge>
                                    <Badge :variant="bill.status === 'paid' ? 'secondary' : 'default'">
                                        {{ bill.status === 'paid' ? 'Paid' : 'Unpaid' }}
                                    </Badge>
                                </CardTitle>
                                <CardDescription v-if="bill.category"> Category: {{ bill.category.name }} </CardDescription>
                            </div>
                            <div class="text-2xl font-bold">
                                {{ formatCurrency(bill.amount) }}
                            </div>
                        </div>
                    </CardHeader>

                    <CardContent>
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <h3 class="text-muted-foreground mb-1 text-sm font-medium">Due Date</h3>
                                <p class="flex items-center" :class="{ 'text-destructive': isPastDue }">
                                    <CalendarIcon class="mr-2 h-4 w-4" />
                                    {{ formatDate(bill.due_date) }}
                                </p>
                            </div>

                            <div v-if="bill.is_recurring">
                                <h3 class="text-muted-foreground mb-1 text-sm font-medium">Recurrence</h3>
                                <p class="flex items-center">
                                    <RotateCcw class="mr-2 h-4 w-4" />
                                    {{
                                        bill.recurrence_period ? bill.recurrence_period.charAt(0).toUpperCase() + bill.recurrence_period.slice(1) : ''
                                    }}
                                </p>
                            </div>

                            <div v-if="bill.created_at">
                                <h3 class="text-muted-foreground mb-1 text-sm font-medium">Created</h3>
                                <p class="flex items-center">
                                    <Clock class="mr-2 h-4 w-4" />
                                    {{ formatDate(bill.created_at) }}
                                </p>
                            </div>
                        </div>

                        <div v-if="bill.description" class="mt-6">
                            <h3 class="text-muted-foreground mb-1 text-sm font-medium">Description</h3>
                            <p class="text-sm">{{ bill.description }}</p>
                        </div>
                    </CardContent>

                    <CardFooter v-if="bill.status === 'unpaid'" class="flex justify-end">
                        <Button @click="openPaymentDialog" :disabled="isLoading">
                            <Receipt class="mr-2 h-4 w-4" />
                            {{ isLoading ? 'Loading...' : 'Record Payment' }}
                        </Button>
                    </CardFooter>
                </Card>

                <!-- Payment History -->
                <div class="mt-8">
                    <TransactionList :transactions="bill.transactions || []" :showBillLink="false" />
                </div>

                <!-- Payment Dialog -->
                <PaymentDialog
                    v-model:isOpen="isPaymentDialogOpen"
                    :bill="bill"
                    :payment-methods="paymentMethods"
                    :next-due-date="nextDueDate"
                    @payment-complete="onPaymentComplete"
                />
            </div>
        </div>
    </AppLayout>
</template>
