<script setup lang="ts">
import Details from '@/components/bills/Details.vue';
import PaymentDialog from '@/components/bills/PaymentDialog.vue';
import Confirm from '@/components/shared/Confirm.vue';
import TransactionList from '@/components/transactions/TransactionList.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatDate } from '@/lib/utils';
import { Bill } from '@/types/model';
import { Head, Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import { Edit, Receipt, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Props {
    bill: Bill;
}

const { bill } = defineProps<Props>();

const isPaymentDialogOpen = ref(false);
const paymentMethods = ref<Record<string, string>>({});
const nextDueDate = ref<string | null>(null);
const isLoading = ref(false);

const isPastDue = computed((): boolean => {
    return new Date(bill.due_date as string) < new Date() && bill.status === 'unpaid';
});

async function openPaymentDialog(): Promise<void> {
    isLoading.value = true;
    try {
        const response = await axios.get(route('bills.payment-details', bill.slug));
        console.log({ response });
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
    <AppLayout
        :breadcrumbs="[
            {
                title: 'Bills',
                href: route('bills.index'),
            },
            {
                title: `Bill Details (${bill.title})`,
                href: route('bills.show', bill.slug),
            },
        ]"
    >
        <Head :title="bill.title" />

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Back Button and Title -->
                <div class="mb-6 flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <Link :href="route('bills.index')" class="text-muted-foreground text-sm hover:underline"> &larr; Back to Bills </Link>
                        <h2 class="mt-1 text-xl font-semibold text-gray-800 dark:text-gray-200">
                            {{ bill.title }}
                        </h2>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <Button variant="outline" asChild>
                            <Link :href="route('bills.invoice', bill.slug)">
                                <Receipt class="mr-2 h-4 w-4" />
                                Generate Invoice
                            </Link>
                        </Button>
                        <Button variant="outline" asChild>
                            <Link :href="route('bills.edit', bill.slug)">
                                <Edit class="mr-2 h-4 w-4" />
                                Edit
                            </Link>
                        </Button>
                        <Confirm :url="route('bills.destroy', bill.slug)" title="Are you sure?" modal>
                            <Button variant="destructive" type="button">
                                <Trash2 class="mr-2 h-4 w-4" />
                                Delete
                            </Button>
                        </Confirm>
                    </div>
                </div>

                <!-- Past Due Alert -->
                <Alert v-if="isPastDue" variant="destructive" class="mb-6">
                    <AlertTitle>Past Due!</AlertTitle>
                    <AlertDescription> This bill was due on {{ formatDate(bill.due_date as string) }} and is still unpaid. </AlertDescription>
                </Alert>

                <!-- Bill Details Card -->
                <Details :bill="bill">
                    <template #footer>
                        <Button @click="openPaymentDialog" :disabled="isLoading">
                            <Receipt class="mr-2 h-4 w-4" />
                            {{ isLoading ? 'Loading...' : 'Record Payment' }}
                        </Button>
                    </template>
                </Details>

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
