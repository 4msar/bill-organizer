<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { formatCurrency, formatDate } from '@/lib/utils';
import { BillAutoTransaction } from '@/types/model';

interface Props {
    isOpen: boolean;
    autoTransaction?: BillAutoTransaction | null;
}

interface Emits {
    (e: 'update:isOpen', value: boolean): void;
}

defineProps<Props>();
const emit = defineEmits<Emits>();

function closeDialog(): void {
    emit('update:isOpen', false);
}
</script>

<template>
    <Dialog :open="isOpen" @update:open="closeDialog">
        <DialogContent class="sm:max-w-[460px]">
            <DialogHeader>
                <DialogTitle>Auto Transaction Details</DialogTitle>
                <DialogDescription> The transaction below will be created automatically on the billing date. </DialogDescription>
            </DialogHeader>

            <div v-if="autoTransaction" class="grid gap-4">
                <div class="rounded-md border p-3">
                    <p class="text-sm font-medium">Amount</p>
                    <p class="text-lg font-semibold">
                        {{ formatCurrency(autoTransaction.amount, $page.props?.team?.current?.currency as string) }}
                    </p>
                </div>
                <div class="rounded-md border p-3">
                    <p class="text-sm font-medium">Payment Method</p>
                    <p class="text-sm">{{ autoTransaction.payment_method || 'N/A' }}</p>
                </div>
                <div class="rounded-md border p-3">
                    <p class="text-sm font-medium">Status</p>
                    <Badge variant="outline">{{ autoTransaction.is_active ? 'Active' : 'Inactive' }}</Badge>
                </div>
                <div v-if="autoTransaction.last_processed_date" class="rounded-md border p-3">
                    <p class="text-sm font-medium">Last Processed</p>
                    <p class="text-sm">{{ formatDate(autoTransaction.last_processed_date) }}</p>
                </div>
                <div v-if="autoTransaction.notes" class="rounded-md border p-3">
                    <p class="text-sm font-medium">Notes</p>
                    <p class="text-sm">{{ autoTransaction.notes }}</p>
                </div>
            </div>

            <DialogFooter>
                <Button type="button" variant="outline" @click="closeDialog">Close</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
