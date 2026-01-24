<script setup lang="ts">
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { Transaction } from '@/types/model';
import { Head, router } from '@inertiajs/vue3';
import { ArrowLeft, Printer } from 'lucide-vue-next';

interface Props {
    transaction: Transaction;
}

const { transaction } = defineProps<Props>();

function printReceipt() {
    window.print();
}

function goBack() {
    router.visit(route('bills.show', transaction.bill_id));
}

function formatDate(date: string) {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
}

function formatTime(date: string) {
    return new Date(date).toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
    });
}
</script>

<template>
    <AppLayout
        :breadcrumbs="[
            { title: 'Bills', href: route('bills.index') },
            { title: transaction.bill?.title || 'Bill', href: route('bills.show', transaction.bill_id) },
            { title: 'Receipt', href: '#' },
        ]"
    >
        <Head title="Payment Receipt" />

        <div class="py-6">
            <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
                <!-- Action Buttons (hidden when printing) -->
                <div class="mb-6 flex items-center justify-between print:hidden">
                    <Button variant="ghost" @click="goBack">
                        <ArrowLeft class="mr-2 h-4 w-4" />
                        Back to Bill
                    </Button>
                    <Button @click="printReceipt">
                        <Printer class="mr-2 h-4 w-4" />
                        Print / Save as PDF
                    </Button>
                </div>

                <!-- Receipt Content -->
                <div
                    id="receipt-content"
                    class="rounded-lg border bg-white p-8 shadow-sm dark:bg-gray-900 print:border-0 print:p-0 print:shadow-none"
                >
                    <!-- Header -->
                    <div class="mb-8 border-b pb-6 text-center">
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">PAYMENT RECEIPT</h1>
                        <p class="text-muted-foreground mt-2">Transaction #{{ transaction.id }}</p>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-500">
                            {{ formatDate(transaction.created_at as string) }} at
                            {{ formatTime(transaction.created_at as string) }}
                        </p>
                    </div>

                    <!-- Receipt Details -->
                    <div class="mb-8 space-y-6">
                        <!-- Bill Information -->
                        <div class="rounded-lg border bg-gray-50 p-4 dark:bg-gray-800">
                            <h3 class="mb-3 text-sm font-semibold text-gray-600 uppercase dark:text-gray-400">Bill Information</h3>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Bill:</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ transaction.bill?.title }}</span>
                                </div>
                                <div v-if="transaction.bill?.category" class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Category:</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ transaction.bill.category.name }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Bill Amount:</span>
                                    <span class="font-medium text-gray-900 dark:text-white">${{ Number(transaction.bill?.amount).toFixed(2) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Details -->
                        <div class="space-y-3">
                            <h3 class="text-sm font-semibold text-gray-600 uppercase dark:text-gray-400">Payment Details</h3>
                            <div class="space-y-2">
                                <div class="flex justify-between border-b pb-2">
                                    <span class="text-gray-600 dark:text-gray-400">Payment Date:</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{
                                        formatDate(transaction.payment_date as string)
                                    }}</span>
                                </div>
                                <div class="flex justify-between border-b pb-2">
                                    <span class="text-gray-600 dark:text-gray-400">Payment Method:</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ transaction.payment_method || 'Not specified' }}</span>
                                </div>
                                <div class="flex justify-between border-b pb-2">
                                    <span class="text-gray-600 dark:text-gray-400">Paid By:</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ transaction.user?.name || 'Unknown' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Amount Paid -->
                        <div class="bg-primary/10 rounded-lg p-6 text-center">
                            <p class="mb-2 text-sm text-gray-600 dark:text-gray-400">Amount Paid</p>
                            <p class="text-primary text-4xl font-bold">${{ Number(transaction.amount).toFixed(2) }}</p>
                        </div>

                        <!-- Notes -->
                        <div v-if="transaction.notes" class="rounded-lg border p-4">
                            <h3 class="mb-2 text-sm font-semibold text-gray-600 uppercase dark:text-gray-400">Notes</h3>
                            <p class="text-sm whitespace-pre-line text-gray-600 dark:text-gray-400">
                                {{ transaction.notes }}
                            </p>
                        </div>

                        <!-- Status Badge -->
                        <div class="text-center">
                            <div class="inline-flex items-center rounded-full border-2 border-green-500 bg-green-50 px-6 py-2 dark:bg-green-900/20">
                                <svg class="mr-2 h-5 w-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="font-semibold text-green-700 dark:text-green-400">Payment Confirmed</span>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="mt-8 border-t pt-6 text-center">
                        <p class="text-xs text-gray-500 dark:text-gray-500">This is an automated receipt for your payment.</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-500">Thank you for your payment!</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
