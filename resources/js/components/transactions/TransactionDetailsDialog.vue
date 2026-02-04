<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { formatCurrency, formatDate, getDocumentType, getPaymentMethodName, isImage, isPdf } from '@/lib/utils';
import { Transaction } from '@/types/model';
import { Calendar, CreditCard, Download, ExternalLink, FileText, Receipt, Trash2 } from 'lucide-vue-next';
import { computed } from 'vue';
import Confirm from '../shared/Confirm.vue';

interface Props {
    open: boolean;
    transaction?: Transaction | null;
}

const props = withDefaults(defineProps<Props>(), {
    transaction: null,
});

const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

const handleClose = () => {
    emit('update:open', false);
};

const paymentMethodName = computed(() => {
    return getPaymentMethodName(props.transaction?.payment_method || null);
});

const paymentMethodIcon = computed(() => {
    return CreditCard; // You could add logic here to return different icons based on payment method
});
</script>

<template>
    <Dialog :open="open" @update:open="handleClose">
        <DialogContent class="max-h-[80vh] overflow-y-auto sm:max-w-[600px]">
            <DialogHeader>
                <DialogTitle class="flex items-center text-xl">
                    <Receipt class="mr-2 h-5 w-5" />
                    Transaction Details
                </DialogTitle>
                <DialogDescription>
                    <div class="flex items-center gap-2">
                        <Calendar class="h-4 w-4" />
                        <span>Payment recorded on {{ formatDate(transaction?.payment_date || '') }}</span>
                    </div>
                </DialogDescription>
            </DialogHeader>

            <div v-if="transaction" class="space-y-6 py-4">
                <!-- Amount Section -->
                <div class="rounded-lg border p-4">
                    <h3 class="mb-2 font-medium">Payment Amount</h3>
                    <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                        {{ formatCurrency(transaction.amount, $page.props?.team?.current?.currency as string) }}
                    </p>
                </div>

                <!-- Payment Details -->
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <h4 class="text-muted-foreground mb-2 text-sm font-medium">Payment Date</h4>
                        <div class="flex items-center">
                            <Calendar class="text-muted-foreground mr-2 h-4 w-4" />
                            {{ formatDate(transaction.payment_date) }}
                        </div>
                    </div>

                    <div>
                        <h4 class="text-muted-foreground mb-2 text-sm font-medium">Payment Method</h4>
                        <div class="flex items-center">
                            <component :is="paymentMethodIcon" class="text-muted-foreground mr-2 h-4 w-4" />
                            {{ paymentMethodName }}
                        </div>
                    </div>
                </div>

                <!-- Bill Information -->
                <div v-if="transaction.bill">
                    <h4 class="text-muted-foreground mb-2 text-sm font-medium">Related Bill</h4>
                    <div class="rounded-lg border p-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium">{{ transaction.bill.title }}</p>
                                <p v-if="transaction.bill.description" class="text-muted-foreground text-sm">
                                    {{ transaction.bill.description }}
                                </p>
                            </div>
                            <Button variant="outline" size="sm" as="a" :href="route('bills.show', transaction?.bill?.slug ?? transaction.bill_id)">
                                <ExternalLink class="mr-1 h-4 w-4" />
                                View Bill
                            </Button>
                        </div>
                    </div>
                </div>

                <!-- Notes Section -->
                <div v-if="transaction.notes">
                    <h4 class="text-muted-foreground mb-2 text-sm font-medium">Notes</h4>
                    <div class="rounded-lg border p-3">
                        <p class="text-sm">{{ transaction.notes }}</p>
                    </div>
                </div>

                <!-- Attachment Section -->
                <div v-if="transaction.attachment">
                    <h4 class="text-muted-foreground mb-2 text-sm font-medium">Attachment</h4>
                    <div class="rounded-lg border p-3">
                        <div class="flex flex-wrap items-center justify-between gap-4">
                            <div class="flex items-center">
                                <component
                                    :is="isImage(transaction.attachment) ? 'Image' : isPdf(transaction.attachment) ? FileText : FileText"
                                    class="text-muted-foreground mr-2 h-5 w-5"
                                />
                                <div>
                                    <p class="font-medium">{{ getDocumentType(transaction.attachment) }}</p>
                                    <p class="text-muted-foreground text-xs">
                                        {{ transaction.attachment.split('/').pop() }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <Button variant="outline" size="sm" as="a" :href="`/storage/${transaction.attachment}`" target="_blank">
                                    <ExternalLink class="mr-1 h-4 w-4" />
                                    View
                                </Button>
                                <Button
                                    v-if="transaction.attachment_link"
                                    variant="outline"
                                    size="sm"
                                    as="a"
                                    :href="transaction.attachment_link"
                                    download
                                >
                                    <Download class="mr-1 h-4 w-4" />
                                    Download
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Metadata -->
                <div class="text-muted-foreground text-xs">
                    <p>Transaction ID: {{ transaction.tnx_id ?? transaction.id }}</p>
                    <p>Created: {{ formatDate(transaction.created_at) }}</p>
                    <p v-if="transaction.updated_at !== transaction.created_at">Last updated: {{ formatDate(transaction.updated_at) }}</p>
                </div>
            </div>

            <DialogFooter class="flex justify-between sm:justify-between">
                <div class="flex gap-2">
                    <Confirm v-if="transaction" :url="route('transactions.destroy', transaction.id)">
                        <Button variant="outline" size="sm" class="text-destructive hover:text-destructive">
                            <Trash2 class="mr-2 h-4 w-4" />
                            Delete
                        </Button>
                    </Confirm>
                    <Button size="sm" class="w-auto" @click="handleClose">Close</Button>
                </div>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
