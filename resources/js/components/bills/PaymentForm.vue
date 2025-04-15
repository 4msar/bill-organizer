<script setup lang="ts">
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import { Form, FormControl, FormDescription, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { Input } from '@/components/ui/input';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import { formatCurrency, formatDate } from '@/lib/utils';
import { useForm } from '@inertiajs/vue3';
import { CalendarIcon, FileText, Receipt } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Category {
    id: number;
    name: string;
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
}

interface Props {
    bill: Bill;
    paymentMethods: Record<string, string>;
    nextDueDate?: string | null;
    cancelRoute?: string;
    showCancelButton?: boolean;
}

interface Emits {
    (e: 'cancel'): void;
    (e: 'submit'): void;
    (e: 'success'): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const selectedDate = ref<Date>(new Date());

const form = useForm({
    bill_id: props.bill.id,
    amount: props.bill.amount,
    payment_date: new Date().toISOString().split('T')[0],
    payment_method: 'cash',
    notes: '',
    attachment: null as File | null,
    update_due_date: true,
});

// Format the payment date
const formattedDate = computed((): string => {
    if (!selectedDate.value) return '';
    return formatDate(selectedDate.value);
});

// Set payment date when calendar date changes
function updatePaymentDate(date: Date): void {
    selectedDate.value = date;
    form.payment_date = date.toISOString().split('T')[0];
}

// Handle file upload
function handleFileUpload(e: Event): void {
    const input = e.target as HTMLInputElement;
    if (input.files && input.files.length > 0) {
        form.attachment = input.files[0];
    }
}

// Form submission
function submitForm(): void {
    form.post(route('transactions.store'), {
        forceFormData: true,
        onSuccess: () => {
            emit('success');
            form.reset();
        },
    });
}

function cancel(): void {
    emit('cancel');
}

// Next due date formatting
const nextDueDateFormatted = computed((): string => {
    if (!props.nextDueDate) return '';
    return formatDate(props.nextDueDate);
});
</script>

<template>
    <Form @submit="submitForm">
        <div class="grid gap-6">
            <!-- Summary -->
            <div class="bg-muted rounded-md p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-medium">{{ bill.title }}</h3>
                        <p class="text-muted-foreground text-sm">Due: {{ formatDate(bill.due_date) }}</p>
                    </div>
                    <div class="text-xl font-bold">
                        {{ formatCurrency(bill.amount) }}
                    </div>
                </div>
            </div>

            <!-- Amount -->
            <FormField v-model="form.amount" name="amount" :validate="(v) => (!!v && v > 0) || 'Amount is required'">
                <FormItem>
                    <FormLabel>Payment Amount</FormLabel>
                    <FormControl>
                        <div class="relative">
                            <span class="text-muted-foreground absolute inset-y-0 left-0 flex items-center pl-3">$</span>
                            <Input v-model="form.amount" type="number" min="0.01" step="0.01" class="pl-8" />
                        </div>
                    </FormControl>
                    <FormMessage />
                </FormItem>
            </FormField>

            <!-- Payment Date -->
            <FormField v-model="form.payment_date" name="payment_date">
                <FormItem>
                    <FormLabel>Payment Date</FormLabel>
                    <Popover>
                        <PopoverTrigger as-child>
                            <FormControl>
                                <Button variant="outline" class="w-full pl-3 text-left font-normal">
                                    <CalendarIcon class="mr-2 h-4 w-4" />
                                    {{ formattedDate }}
                                </Button>
                            </FormControl>
                        </PopoverTrigger>
                        <PopoverContent class="w-auto p-0" align="start">
                            <Calendar :selected-date="selectedDate" @update:selected-date="updatePaymentDate" />
                        </PopoverContent>
                    </Popover>
                    <FormMessage />
                </FormItem>
            </FormField>

            <!-- Payment Method -->
            <FormField v-model="form.payment_method" name="payment_method">
                <FormItem>
                    <FormLabel>Payment Method</FormLabel>
                    <Select v-model="form.payment_method">
                        <FormControl>
                            <SelectTrigger>
                                <SelectValue placeholder="Select payment method" />
                            </SelectTrigger>
                        </FormControl>
                        <SelectContent>
                            <SelectItem v-for="(label, value) in paymentMethods" :key="value" :value="value">
                                {{ label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <FormMessage />
                </FormItem>
            </FormField>

            <!-- Attachment -->
            <FormField v-model="form.attachment" name="attachment">
                <FormItem>
                    <FormLabel>Attachment (Optional)</FormLabel>
                    <FormControl>
                        <div class="grid w-full items-center gap-1.5">
                            <Input type="file" @change="handleFileUpload" accept="image/*,.pdf,.doc,.docx" />
                        </div>
                    </FormControl>
                    <FormDescription> Upload receipt or proof of payment (max 10MB) </FormDescription>
                    <FormMessage />
                </FormItem>
            </FormField>

            <!-- Notes -->
            <FormField v-model="form.notes" name="notes">
                <FormItem>
                    <FormLabel>Notes (Optional)</FormLabel>
                    <FormControl>
                        <Textarea v-model="form.notes" placeholder="Add any notes about this payment" rows="3" />
                    </FormControl>
                    <FormMessage />
                </FormItem>
            </FormField>

            <!-- Next Due Date for Recurring Bills -->
            <div v-if="bill.is_recurring">
                <Alert variant="info" class="mb-3">
                    <FileText class="h-4 w-4" />
                    <AlertTitle>Recurring Bill</AlertTitle>
                    <AlertDescription>
                        This is a {{ bill.recurrence_period }} recurring bill. Would you like to automatically create the next bill?
                    </AlertDescription>
                </Alert>

                <FormField v-model="form.update_due_date" name="update_due_date">
                    <FormItem class="flex flex-row items-center justify-between rounded-lg border p-4">
                        <div class="space-y-0.5">
                            <FormLabel>Create Next Bill</FormLabel>
                            <FormDescription v-if="nextDueDate"> Next due date will be {{ nextDueDateFormatted }} </FormDescription>
                        </div>
                        <FormControl>
                            <Switch v-model="form.update_due_date" />
                        </FormControl>
                    </FormItem>
                </FormField>
            </div>

            <!-- Buttons -->
            <div class="flex justify-end gap-2">
                <Button v-if="showCancelButton" type="button" variant="outline" @click="cancel"> Cancel </Button>
                <Button type="submit" :disabled="form.processing">
                    <Receipt class="mr-2 h-4 w-4" />
                    Record Payment
                </Button>
            </div>
        </div>
    </Form>
</template>
