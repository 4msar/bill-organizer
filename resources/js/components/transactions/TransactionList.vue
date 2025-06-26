<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { formatCurrency, formatDate } from '@/lib/utils';
import { Transaction } from '@/types/model';
import { Calendar, CreditCard, Download, ExternalLink, FileText, Receipt, Trash2 } from 'lucide-vue-next';
import Confirm from '../shared/Confirm.vue';

interface Props {
    transactions: Transaction[];
    showBillLink?: boolean;
}

defineProps<Props>();

const methods = {
    cash: 'Cash',
    credit_card: 'Credit Card',
    debit_card: 'Debit Card',
    bank_transfer: 'Bank Transfer',
    paypal: 'PayPal',
    crypto: 'Cryptocurrency',
    check: 'Check',
    other: 'Other',
};

function getPaymentMethodName(method: string | null): string {
    if (!method) return 'Other';
    return methods[method as keyof typeof methods] || method;
}

function getPaymentMethodIcon(method: string | null) {
    if (method) {
        return CreditCard;
    }

    return CreditCard;
}

function getFileExtension(path: string): string {
    if (!path) return '';
    const parts = path.split('.');
    return parts[parts.length - 1].toLowerCase();
}

function isImage(path: string): boolean {
    const ext = getFileExtension(path);
    return ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'].includes(ext);
}

function isPdf(path: string): boolean {
    return getFileExtension(path) === 'pdf';
}

function getDocumentType(path: string): string {
    const ext = getFileExtension(path);
    if (isImage(path)) return 'Image';
    if (isPdf(path)) return 'PDF';
    return ext.toUpperCase();
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
                        <TableRow v-for="transaction in transactions" :key="transaction.id">
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
                                <TooltipProvider v-if="transaction.attachment">
                                    <Tooltip>
                                        <TooltipTrigger asChild>
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                class="h-8 px-2"
                                                as="a"
                                                :href="`/storage/${transaction.attachment}`"
                                                target="_blank"
                                            >
                                                <component
                                                    :is="
                                                        isImage(transaction.attachment)
                                                            ? 'Image'
                                                            : isPdf(transaction.attachment)
                                                              ? FileText
                                                              : FileText
                                                    "
                                                    class="mr-1 h-4 w-4"
                                                />
                                                <span class="sr-only md:not-sr-only md:text-xs">View</span>
                                            </Button>
                                        </TooltipTrigger>
                                        <TooltipContent>
                                            <div class="text-xs">{{ getDocumentType(transaction.attachment) }} attachment</div>
                                        </TooltipContent>
                                    </Tooltip>
                                </TooltipProvider>
                                <span v-else class="text-muted-foreground text-sm">—</span>
                            </TableCell>
                            <TableCell>
                                <TooltipProvider v-if="transaction.notes">
                                    <Tooltip>
                                        <TooltipTrigger asChild>
                                            <Button variant="ghost" size="sm" class="h-8 px-2">
                                                <FileText class="h-4 w-4" />
                                                <span class="sr-only">Notes</span>
                                            </Button>
                                        </TooltipTrigger>
                                        <TooltipContent>
                                            <div class="max-w-xs text-xs">
                                                {{ transaction.notes }}
                                            </div>
                                        </TooltipContent>
                                    </Tooltip>
                                </TooltipProvider>
                                <span v-else class="text-muted-foreground text-sm">—</span>
                            </TableCell>
                            <TableCell class="text-right">
                                <div class="flex justify-end space-x-1">
                                    <Button
                                        v-if="transaction.attachment"
                                        variant="ghost"
                                        size="icon"
                                        class="h-8 w-8"
                                        as="a"
                                        :href="`/storage/${transaction.attachment}`"
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
</template>
