<script setup lang="ts">
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Bill } from '@/types/model';
import PaymentForm from './PaymentForm.vue';

interface Props {
    isOpen: boolean;
    bill: Bill;
    paymentMethods: Record<string, string>;
    nextDueDate?: string | null;
}

interface Emits {
    (e: 'update:isOpen', value: boolean): void;
    (e: 'paymentComplete'): void;
}

defineProps<Props>();
const emit = defineEmits<Emits>();

function closeDialog(): void {
    emit('update:isOpen', false);
}

function onFormSuccess(): void {
    emit('update:isOpen', false);
    emit('paymentComplete');
}
</script>

<template>
    <Dialog :open="isOpen" @update:open="closeDialog">
        <DialogContent class="max-h-[90dvh] grid-rows-[auto_minmax(0,1fr)_auto] p-0 sm:max-w-[500px]">
            <DialogHeader class="p-6 pb-0">
                <DialogTitle>Record Payment</DialogTitle>
                <DialogDescription v-if="bill"> Record payment for {{ bill.title }} </DialogDescription>
            </DialogHeader>

            <div class="overflow-y-auto p-6">
                <PaymentForm
                    :bill="bill"
                    :payment-methods="paymentMethods"
                    :next-due-date="nextDueDate"
                    :show-cancel-button="true"
                    @cancel="closeDialog"
                    @success="onFormSuccess"
                />
            </div>
        </DialogContent>
    </Dialog>
</template>
