<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { useLocalStorage } from '@/composables/useLocalStorage';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatCurrency } from '@/lib/utils';
import { Bill } from '@/types/model';
import { Head, router } from '@inertiajs/vue3';
import { ArrowLeft, Eye, EyeOff, Plus, Printer, RotateCcw, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Props {
    bill: Bill;
}

const { bill } = defineProps<Props>();

interface InvoiceItem {
    description: string;
    quantity: number;
    unit_price: number;
}

interface InvoiceFormData {
    invoice_number: string;
    invoice_date: string;
    due_date: string;
    from_name: string;
    from_email: string;
    from_phone: string;
    from_address: string;
    to_name: string;
    to_email: string;
    to_phone: string;
    to_address: string;
    items: InvoiceItem[];
    tax_rate: number;
    discount: number;
    notes: string;
}

const showPreview = ref(false);

// Default form data
const getDefaultFormData = (): InvoiceFormData => ({
    invoice_number: `INV-${Date.now()}`,
    invoice_date: new Date().toISOString().split('T')[0],
    due_date: '',
    from_name: '',
    from_email: '',
    from_phone: '',
    from_address: '',
    to_name: '',
    to_email: '',
    to_phone: '',
    to_address: '',
    items: [
        {
            description: bill.title || '',
            quantity: 1,
            unit_price: Number(bill.amount) || 0,
        },
    ],
    tax_rate: 0,
    discount: 0,
    notes: bill.description || '',
});

// Use localStorage to persist form data for this bill
const storageKey = `invoice-form-bill-${bill.id}`;
const formData = useLocalStorage<InvoiceFormData>(storageKey, getDefaultFormData());

const subtotal = computed(() => {
    return formData.value.items.reduce((sum, item) => {
        return sum + item.quantity * item.unit_price;
    }, 0);
});

const taxAmount = computed(() => {
    return (subtotal.value * formData.value.tax_rate) / 100;
});

const total = computed(() => {
    return subtotal.value + taxAmount.value - formData.value.discount;
});

function addItem() {
    formData.value.items.push({
        description: '',
        quantity: 1,
        unit_price: 0,
    });
}

function removeItem(index: number) {
    if (formData.value.items.length > 1) {
        formData.value.items.splice(index, 1);
    }
}

function resetForm() {
    const confirmed = confirm('Are you sure you want to reset the form? All your changes will be lost.');
    if (confirmed) {
        formData.value = getDefaultFormData();
    }
}

function togglePreview() {
    showPreview.value = !showPreview.value;
}

function printInvoice() {
    window.print();
}

function goBack() {
    router.visit(route('bills.show', bill.id));
}

function formatDate(date: string) {
    if (!date) return '';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
}
</script>

<template>
    <AppLayout
        :breadcrumbs="[
            { title: 'Bills', href: route('bills.index') },
            { title: bill.title, href: route('bills.show', bill.id) },
            { title: 'Generate Invoice', href: route('bills.invoice', bill.id) },
        ]"
    >
        <Head title="Generate Invoice" />

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <Button variant="ghost" @click="goBack" class="mb-2 print:hidden">
                            <ArrowLeft class="mr-2 h-4 w-4" />
                            Back to Bill
                        </Button>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Generate Invoice</h2>
                        <p class="text-muted-foreground text-sm">Create an invoice for {{ bill.title }}</p>
                    </div>
                    <div class="flex gap-2 print:hidden">
                        <Button variant="outline" @click="togglePreview">
                            <component :is="showPreview ? EyeOff : Eye" class="mr-2 h-4 w-4" />
                            {{ showPreview ? 'Hide Preview' : 'Show Preview' }}
                        </Button>
                        <Button v-if="showPreview" @click="printInvoice">
                            <Printer class="mr-2 h-4 w-4" />
                            Print
                        </Button>
                    </div>
                </div>

                <div class="grid gap-6" :class="showPreview ? 'lg:grid-cols-2' : 'grid-cols-1'">
                    <!-- Form -->
                    <div class="space-y-6 print:hidden">
                        <form @submit.prevent="togglePreview" class="space-y-6">
                            <!-- Invoice Details -->
                            <div class="bg-card rounded-lg border p-6">
                                <h3 class="mb-4 text-lg font-semibold">Invoice Details</h3>
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <Label for="invoice_number">Invoice Number *</Label>
                                        <Input id="invoice_number" v-model="formData.invoice_number" required />
                                    </div>
                                    <div>
                                        <Label for="invoice_date">Invoice Date *</Label>
                                        <Input id="invoice_date" v-model="formData.invoice_date" type="date" required />
                                    </div>
                                    <div>
                                        <Label for="due_date">Due Date</Label>
                                        <Input id="due_date" v-model="formData.due_date" type="date" />
                                    </div>
                                </div>
                            </div>

                            <!-- From (Sender) -->
                            <div class="bg-card rounded-lg border p-6">
                                <h3 class="mb-4 text-lg font-semibold">From (Your Information)</h3>
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <Label for="from_name">Name *</Label>
                                        <Input id="from_name" v-model="formData.from_name" required />
                                    </div>
                                    <div>
                                        <Label for="from_email">Email</Label>
                                        <Input id="from_email" v-model="formData.from_email" type="email" />
                                    </div>
                                    <div>
                                        <Label for="from_phone">Phone</Label>
                                        <Input id="from_phone" v-model="formData.from_phone" />
                                    </div>
                                    <div class="sm:col-span-2">
                                        <Label for="from_address">Address</Label>
                                        <Textarea id="from_address" v-model="formData.from_address" rows="2" />
                                    </div>
                                </div>
                            </div>

                            <!-- To (Customer) -->
                            <div class="bg-card rounded-lg border p-6">
                                <h3 class="mb-4 text-lg font-semibold">To (Customer Information)</h3>
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <Label for="to_name">Name *</Label>
                                        <Input id="to_name" v-model="formData.to_name" required />
                                    </div>
                                    <div>
                                        <Label for="to_email">Email</Label>
                                        <Input id="to_email" v-model="formData.to_email" type="email" />
                                    </div>
                                    <div>
                                        <Label for="to_phone">Phone</Label>
                                        <Input id="to_phone" v-model="formData.to_phone" />
                                    </div>
                                    <div class="sm:col-span-2">
                                        <Label for="to_address">Address</Label>
                                        <Textarea id="to_address" v-model="formData.to_address" rows="2" />
                                    </div>
                                </div>
                            </div>

                            <!-- Items -->
                            <div class="bg-card rounded-lg border p-6">
                                <div class="mb-4 flex items-center justify-between">
                                    <h3 class="text-lg font-semibold">Items</h3>
                                    <Button type="button" variant="outline" size="sm" @click="addItem">
                                        <Plus class="mr-2 h-4 w-4" />
                                        Add Item
                                    </Button>
                                </div>
                                <div class="space-y-4">
                                    <div
                                        v-for="(item, index) in formData.items"
                                        :key="index"
                                        class="grid gap-4 rounded-lg border p-4 sm:grid-cols-12"
                                    >
                                        <div class="sm:col-span-5">
                                            <Label :for="`item_desc_${index}`">Description *</Label>
                                            <Input :id="`item_desc_${index}`" v-model="item.description" required />
                                        </div>
                                        <div class="sm:col-span-2">
                                            <Label :for="`item_qty_${index}`">Quantity *</Label>
                                            <Input
                                                :id="`item_qty_${index}`"
                                                v-model.number="item.quantity"
                                                type="number"
                                                min="1"
                                                step="any"
                                                required
                                            />
                                        </div>
                                        <div class="sm:col-span-3">
                                            <Label :for="`item_price_${index}`">Unit Price *</Label>
                                            <Input
                                                :id="`item_price_${index}`"
                                                v-model.number="item.unit_price"
                                                type="number"
                                                min="0"
                                                step="0.01"
                                                required
                                            />
                                        </div>
                                        <div class="flex items-end sm:col-span-2">
                                            <div class="flex w-full items-center justify-between">
                                                <div class="text-sm font-medium">
                                                    {{
                                                        formatCurrency(
                                                            item.quantity * item.unit_price,
                                                            $page.props?.team?.current?.currency as string,
                                                        )
                                                    }}
                                                </div>
                                                <Button
                                                    type="button"
                                                    variant="ghost"
                                                    size="sm"
                                                    @click="removeItem(index)"
                                                    :disabled="formData.items.length === 1"
                                                >
                                                    <Trash2 class="text-destructive h-4 w-4" />
                                                </Button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Calculations -->
                            <div class="bg-card rounded-lg border p-6">
                                <h3 class="mb-4 text-lg font-semibold">Additional Charges</h3>
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <Label for="tax_rate">Tax Rate (%)</Label>
                                        <Input id="tax_rate" v-model.number="formData.tax_rate" type="number" min="0" step="0.01" />
                                    </div>
                                    <div>
                                        <Label for="discount">Discount ({{ $page.props?.team?.current?.currency_symbol }})</Label>
                                        <Input id="discount" v-model.number="formData.discount" type="number" min="0" step="0.01" />
                                    </div>
                                </div>
                                <div class="mt-6 space-y-2 border-t pt-4">
                                    <div class="flex justify-between text-sm">
                                        <span>Subtotal:</span>
                                        <span class="font-medium">{{
                                            formatCurrency(subtotal, $page.props?.team?.current?.currency as string)
                                        }}</span>
                                    </div>
                                    <div v-if="formData.tax_rate > 0" class="flex justify-between text-sm">
                                        <span>Tax ({{ formData.tax_rate }}%):</span>
                                        <span class="font-medium">{{
                                            formatCurrency(taxAmount, $page.props?.team?.current?.currency as string)
                                        }}</span>
                                    </div>
                                    <div v-if="formData.discount > 0" class="flex justify-between text-sm">
                                        <span>Discount:</span>
                                        <span class="font-medium"
                                            >-{{ formatCurrency(formData.discount, $page.props?.team?.current?.currency as string) }}</span
                                        >
                                    </div>
                                    <div class="flex justify-between border-t pt-2 text-lg font-bold">
                                        <span>Total:</span>
                                        <span>{{ formatCurrency(total, $page.props?.team?.current?.currency as string) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Notes -->
                            <div class="bg-card rounded-lg border p-6">
                                <h3 class="mb-4 text-lg font-semibold">Notes</h3>
                                <Textarea id="notes" v-model="formData.notes" rows="4" placeholder="Add any additional notes..." />
                            </div>

                            <!-- Actions -->
                            <div class="flex justify-end gap-4">
                                <Button type="button" variant="outline" @click="goBack">Cancel</Button>
                                <Button type="button" variant="outline" @click="resetForm">
                                    <RotateCcw class="mr-2 h-4 w-4" />
                                    Reset
                                </Button>
                                <Button variant="default" type="button" @click="togglePreview">
                                    <component :is="showPreview ? EyeOff : Eye" class="mr-2 h-4 w-4" />
                                    {{ showPreview ? 'Hide' : 'View' }}
                                </Button>
                            </div>
                        </form>
                    </div>

                    <!-- Preview -->
                    <div v-if="showPreview" class="print:block">
                        <div
                            id="invoice-content"
                            class="rounded-lg border bg-white p-8 shadow-sm dark:bg-gray-900 print:border-0 print:p-0 print:shadow-none"
                        >
                            <!-- Header -->
                            <div class="mb-8 border-b pb-8">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <h1 class="text-4xl font-bold text-gray-900 dark:text-white">INVOICE</h1>
                                        <p class="text-muted-foreground mt-1">{{ formData.invoice_number }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Invoice Date</p>
                                        <p class="font-medium">{{ formatDate(formData.invoice_date) }}</p>
                                        <p v-if="formData.due_date" class="mt-2 text-sm text-gray-600 dark:text-gray-400">Due Date</p>
                                        <p v-if="formData.due_date" class="font-medium">
                                            {{ formatDate(formData.due_date) }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- From & To -->
                            <div class="mb-8 grid grid-cols-2 gap-8">
                                <!-- From -->
                                <div>
                                    <h3 class="mb-2 text-sm font-semibold text-gray-600 uppercase dark:text-gray-400">From</h3>
                                    <div class="space-y-1">
                                        <p class="font-medium text-gray-900 dark:text-white">
                                            {{ formData.from_name || 'Your Name' }}
                                        </p>
                                        <p v-if="formData.from_email" class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ formData.from_email }}
                                        </p>
                                        <p v-if="formData.from_phone" class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ formData.from_phone }}
                                        </p>
                                        <p v-if="formData.from_address" class="text-sm whitespace-pre-line text-gray-600 dark:text-gray-400">
                                            {{ formData.from_address }}
                                        </p>
                                    </div>
                                </div>

                                <!-- To -->
                                <div>
                                    <h3 class="mb-2 text-sm font-semibold text-gray-600 uppercase dark:text-gray-400">Bill To</h3>
                                    <div class="space-y-1">
                                        <p class="font-medium text-gray-900 dark:text-white">
                                            {{ formData.to_name || 'Customer Name' }}
                                        </p>
                                        <p v-if="formData.to_email" class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ formData.to_email }}
                                        </p>
                                        <p v-if="formData.to_phone" class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ formData.to_phone }}
                                        </p>
                                        <p v-if="formData.to_address" class="text-sm whitespace-pre-line text-gray-600 dark:text-gray-400">
                                            {{ formData.to_address }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Items Table -->
                            <div class="mb-8">
                                <table class="w-full">
                                    <thead>
                                        <tr class="border-b-2 border-gray-300 dark:border-gray-700">
                                            <th class="pb-3 text-left text-sm font-semibold text-gray-600 uppercase dark:text-gray-400">
                                                Description
                                            </th>
                                            <th class="pb-3 text-right text-sm font-semibold text-gray-600 uppercase dark:text-gray-400">Qty</th>
                                            <th class="pb-3 text-right text-sm font-semibold text-gray-600 uppercase dark:text-gray-400">Price</th>
                                            <th class="pb-3 text-right text-sm font-semibold text-gray-600 uppercase dark:text-gray-400">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="(item, index) in formData.items"
                                            :key="index"
                                            class="border-b border-gray-200 dark:border-gray-800"
                                        >
                                            <td class="py-3 text-gray-900 dark:text-white">{{ item.description }}</td>
                                            <td class="py-3 text-right text-gray-900 dark:text-white">{{ item.quantity }}</td>
                                            <td class="py-3 text-right text-gray-900 dark:text-white">
                                                {{ formatCurrency(item.unit_price, $page.props?.team?.current?.currency as string) }}
                                            </td>
                                            <td class="py-3 text-right font-medium text-gray-900 dark:text-white">
                                                {{ formatCurrency(item.quantity * item.unit_price, $page.props?.team?.current?.currency as string) }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Totals -->
                            <div class="flex justify-end">
                                <div class="w-64 space-y-2">
                                    <div class="flex justify-between border-b pb-2">
                                        <span class="text-gray-600 dark:text-gray-400">Subtotal:</span>
                                        <span class="font-medium text-gray-900 dark:text-white">{{
                                            formatCurrency(subtotal, $page.props?.team?.current?.currency as string)
                                        }}</span>
                                    </div>
                                    <div v-if="formData.tax_rate && formData.tax_rate > 0" class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Tax ({{ formData.tax_rate }}%):</span>
                                        <span class="font-medium text-gray-900 dark:text-white">{{
                                            formatCurrency(taxAmount, $page.props?.team?.current?.currency as string)
                                        }}</span>
                                    </div>
                                    <div v-if="formData.discount && formData.discount > 0" class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Discount:</span>
                                        <span class="font-medium text-gray-900 dark:text-white"
                                            >-{{ formatCurrency(formData.discount, $page.props?.team?.current?.currency as string) }}</span
                                        >
                                    </div>
                                    <div class="flex justify-between border-t-2 border-gray-300 pt-2 dark:border-gray-700">
                                        <span class="text-lg font-bold text-gray-900 dark:text-white">Total:</span>
                                        <span class="text-lg font-bold text-gray-900 dark:text-white">{{
                                            formatCurrency(total, $page.props?.team?.current?.currency as string)
                                        }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Notes -->
                            <div v-if="formData.notes" class="mt-8 border-t pt-6">
                                <h3 class="mb-2 text-sm font-semibold text-gray-600 uppercase dark:text-gray-400">Notes</h3>
                                <p class="text-sm whitespace-pre-line text-gray-600 dark:text-gray-400">
                                    {{ formData.notes }}
                                </p>
                            </div>

                            <!-- Footer -->
                            <div class="mt-12 border-t pt-6 text-center">
                                <p class="text-xs text-gray-500 dark:text-gray-500">Thank you for your business!</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
