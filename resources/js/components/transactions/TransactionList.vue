<script setup lang="ts">
import Tooltip from '@/components/shared/Tooltip.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { formatCurrency, formatDate, getDocumentType, getPaymentMethodName, isImage, isPdf } from '@/lib/utils';
import { Transaction } from '@/types/model';
import { Calendar, CreditCard, Download, ExternalLink, FileText, Receipt, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';
import Confirm from '../shared/Confirm.vue';
import TransactionDetailsDialog from './TransactionDetailsDialog.vue';

interface Props {
    transactions: Transaction[];
    showBillLink?: boolean;
}

defineProps<Props>();

// Dialog state
const showDetailsDialog = ref(false);
const selectedTransaction = ref<Transaction | null>(null);

function getPaymentMethodIcon(method: string | null) {
    if (method) {
        return CreditCard;
    }

    return CreditCard;
}

function openTransactionDetails(transaction: Transaction): void {
    selectedTransaction.value = transaction;
    showDetailsDialog.value = true;
}
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle class="flex items-center text-xl">
                <Receipt class="mr-2 h-5 w-5" />
                Payment History
            </CardTitle>
            <CardDescription v-if="transactions.length > 0">
                {{ transactions.length }} transaction{{ transactions.length !== 1 ? 's' : '' }} recorded
            </CardDescription>
        </CardHeader>

        <CardContent>
            <div v-if="transactions.length === 0" class="py-8 text-center">
                <div class="bg-muted mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full">
                    <Receipt class="text-muted-foreground h-6 w-6" />
                </div>
                <h3 class="mb-1 text-lg font-medium">No Payments Recorded</h3>
                <p class="text-muted-foreground mx-auto max-w-md text-sm">
                    When you record a payment for this bill, the transaction will appear here.
                </p>
            </div>

            <div v-else>
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Date</TableHead>
                            <TableHead>Amount</TableHead>
                            <TableHead>Method</TableHead>
                            <TableHead>Attachment</TableHead>
                            <TableHead>Notes</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="transaction in transactions"
                            :key="transaction.id"
                            class="hover:bg-muted/50 cursor-pointer"
                            @click="openTransactionDetails(transaction)"
                        >
                            <TableCell>
                                <div class="flex items-center">
                                    <Calendar class="text-muted-foreground mr-2 h-4 w-4" />
                                    {{ formatDate(transaction.payment_date) }}
                                </div>
                            </TableCell>
                            <TableCell class="font-medium">
                                {{ formatCurrency(transaction.amount, $page.props?.team?.current?.currency as string) }}
                            </TableCell>
                            <TableCell>
                                <div class="flex items-center">
                                    <component :is="getPaymentMethodIcon(transaction.payment_method)" class="text-muted-foreground mr-2 h-4 w-4" />
                                    {{ getPaymentMethodName(transaction.payment_method) }}
                                </div>
                            </TableCell>
                            <TableCell>
                                <Tooltip v-if="transaction.attachment" :title="getDocumentType(transaction.attachment)">
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        class="h-8 px-2"
                                        as="a"
                                        :href="`/storage/${transaction.attachment}`"
                                        target="_blank"
                                        @click.stop
                                    >
                                        <component
                                            :is="isImage(transaction.attachment) ? 'Image' : isPdf(transaction.attachment) ? FileText : FileText"
                                            class="mr-1 h-4 w-4"
                                        />
                                        <span class="sr-only md:not-sr-only md:text-xs">View</span>
                                    </Button>
                                </Tooltip>
                                <span v-else class="text-muted-foreground text-sm">—</span>
                            </TableCell>
                            <TableCell>
                                <Tooltip v-if="transaction.notes" :title="transaction.notes">
                                    <Button variant="ghost" size="sm" class="h-8 px-2" @click.stop>
                                        <FileText class="h-4 w-4" />
                                        <span class="sr-only">Notes</span>
                                    </Button>
                                </Tooltip>
                                <span v-else class="text-muted-foreground text-sm">—</span>
                            </TableCell>
                            <TableCell class="text-right">
                                <div class="flex justify-end space-x-1" @click.stop>
                                    <Tooltip title="View Receipt">
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            class="h-8 w-8"
                                            as="a"
                                            :href="route('transactions.receipt', transaction.id)"
                                        >
                                            <Receipt class="h-4 w-4" />
                                            <span class="sr-only">Receipt</span>
                                        </Button>
                                    </Tooltip>
                                    <Button
                                        v-if="transaction.attachment_link"
                                        variant="ghost"
                                        size="icon"
                                        class="h-8 w-8"
                                        as="a"
                                        :href="transaction.attachment_link"
                                        download
                                    >
                                        <Download class="h-4 w-4" />
                                        <span class="sr-only">Download</span>
                                    </Button>
                                    <Button
                                        v-if="showBillLink"
                                        variant="ghost"
                                        size="icon"
                                        class="h-8 w-8"
                                        as="a"
                                        :href="route('bills.show', transaction.bill_id)"
                                    >
                                        <ExternalLink class="h-4 w-4" />
                                        <span class="sr-only">View Bill</span>
                                    </Button>
                                    <Confirm :url="route('transactions.destroy', transaction.id)">
                                        <Button variant="ghost" size="icon" class="text-destructive h-8 w-8">
                                            <Trash2 class="h-4 w-4" />
                                            <span class="sr-only">Delete</span>
                                        </Button>
                                    </Confirm>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </CardContent>
    </Card>

    <TransactionDetailsDialog v-model:open="showDetailsDialog" :transaction="selectedTransaction" />
</template>
