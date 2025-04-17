<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import { Form, FormControl, FormDescription, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { Input } from '@/components/ui/input';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import { formatDate } from '@/lib/utils';
import { useForm } from '@inertiajs/vue3';
import { CalendarDate, parseDate } from '@internationalized/date';
import { CalendarIcon } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';

interface Category {
    id: number;
    name: string;
}

interface BillData {
    id?: number;
    title: string;
    description: string | null;
    amount: number | string;
    due_date: string | null | CalendarDate;
    category_id: number | null;
    is_recurring: boolean;
    recurrence_period: 'weekly' | 'monthly' | 'yearly' | null;
    [key: string]: any;
}

interface Props {
    categories: Category[];
    bill: BillData;
    submitUrl: string;
    submitMethod: 'post' | 'put';
}

const props = defineProps<Props>();

const form = useForm<BillData>({
    title: props.bill.title,
    description: props.bill.description,
    amount: props.bill.amount,
    due_date: props.bill.due_date,
    category_id: props.bill.category_id,
    is_recurring: props.bill.is_recurring,
    recurrence_period: props.bill.recurrence_period,
});

const today = new Date();
const dueDate = ref<Date | null>(props.bill.due_date ? new Date(props.bill.due_date as string) : today);

const formattedDate = computed((): string => {
    if (!dueDate.value) return '';
    return formatDate(dueDate.value);
});

function updateDueDate(date: Date): void {
    dueDate.value = date;
    form.due_date = date.toISOString().split('T')[0];
}

onMounted(() => {
    if (props.bill.due_date) {
        form.due_date = parseDate(props.bill.due_date as string);
    } else {
        form.due_date = parseDate(today.toISOString());
    }
});

function submit(): void {
    form[props.submitMethod](props.submitUrl);
}
</script>

<template>
    <Form @submit="submit">
        <div class="grid gap-6">
            <!-- Title -->
            <FormField v-model="form.title" name="title" :validate="(v) => !!v || 'Title is required'">
                <FormItem>
                    <FormLabel>Title</FormLabel>
                    <FormControl>
                        <Input v-model="form.title" placeholder="Enter bill title" />
                    </FormControl>
                    <FormMessage />
                </FormItem>
            </FormField>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <!-- Amount -->
                <FormField v-model="form.amount" name="amount" :validate="(v) => !!v || 'Amount is required'">
                    <FormItem>
                        <FormLabel>Amount</FormLabel>
                        <FormControl>
                            <div class="relative">
                                <span class="text-muted-foreground absolute inset-y-0 left-0 flex items-center pl-3">$</span>
                                <Input v-model="form.amount" type="number" min="0" step="0.01" placeholder="0.00" class="pl-8" />
                            </div>
                        </FormControl>
                        <FormMessage />
                    </FormItem>
                </FormField>

                <!-- Due Date -->
                <FormField v-model="form.due_date" name="due_date">
                    <FormItem>
                        <FormLabel>Due Date</FormLabel>
                        <Popover>
                            <PopoverTrigger as-child>
                                <FormControl>
                                    <Button
                                        variant="outline"
                                        class="w-full pl-3 text-left font-normal"
                                        :class="!dueDate ? 'text-muted-foreground' : ''"
                                    >
                                        <CalendarIcon class="mr-2 h-4 w-4" />
                                        {{ formattedDate || 'Select date' }}
                                    </Button>
                                </FormControl>
                            </PopoverTrigger>
                            <PopoverContent class="w-auto p-0" align="start">
                                <Calendar :model-value="form.due_date" @update:selected-date="updateDueDate" :min-date="today" />
                            </PopoverContent>
                        </Popover>
                        <FormMessage />
                    </FormItem>
                </FormField>
            </div>

            <!-- Category -->
            <FormField v-model="form.category_id" name="category_id">
                <FormItem>
                    <FormLabel>Category</FormLabel>
                    <Select v-model="form.category_id">
                        <FormControl>
                            <SelectTrigger>
                                <SelectValue placeholder="Select a category" />
                            </SelectTrigger>
                        </FormControl>
                        <SelectContent>
                            <SelectItem :value="null">Uncategorized</SelectItem>
                            <SelectItem v-for="category in categories" :key="category.id" :value="category.id">
                                {{ category.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <FormMessage />
                </FormItem>
            </FormField>

            <!-- Recurring Bill -->
            <FormField v-model="form.is_recurring" name="is_recurring">
                <FormItem class="flex flex-row items-center justify-between rounded-lg border p-4">
                    <div class="space-y-0.5">
                        <FormLabel class="text-base">Recurring Bill</FormLabel>
                        <FormDescription> Is this a recurring payment like a subscription? </FormDescription>
                    </div>
                    <FormControl>
                        <Switch v-model="form.is_recurring" />
                    </FormControl>
                </FormItem>
            </FormField>

            <!-- Recurrence Period (conditional) -->
            <FormField v-if="form.is_recurring" v-model="form.recurrence_period" name="recurrence_period">
                <FormItem>
                    <FormLabel>Recurrence Period</FormLabel>
                    <Select v-model="form.recurrence_period">
                        <FormControl>
                            <SelectTrigger>
                                <SelectValue placeholder="Select how often this bill repeats" />
                            </SelectTrigger>
                        </FormControl>
                        <SelectContent>
                            <SelectItem value="weekly">Weekly</SelectItem>
                            <SelectItem value="monthly">Monthly</SelectItem>
                            <SelectItem value="yearly">Yearly</SelectItem>
                        </SelectContent>
                    </Select>
                    <FormMessage />
                </FormItem>
            </FormField>

            <!-- Description -->
            <FormField v-model="form.description" name="description">
                <FormItem>
                    <FormLabel>Description (Optional)</FormLabel>
                    <FormControl>
                        <Textarea v-model="form.description" placeholder="Add any additional details about this bill" rows="3" />
                    </FormControl>
                </FormItem>
            </FormField>

            <!-- Submit Button -->
            <Button type="submit" class="w-full" :disabled="form.processing">
                {{ props.bill.id ? 'Update Bill' : 'Add Bill' }}
            </Button>
        </div>
    </Form>
</template>
