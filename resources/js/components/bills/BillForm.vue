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
import { Bill, Category } from '@/types/model';
import { VisitOptions } from '@inertiajs/core';
import { useForm } from '@inertiajs/vue3';
import { DateValue, getLocalTimeZone, parseDate, today } from '@internationalized/date';
import { format } from 'date-fns';
import { CalendarIcon } from 'lucide-vue-next';
import { capitalize, computed, onMounted } from 'vue';
import TagInput from '../shared/TagInput.vue';
import { Label } from '../ui/label';

export type BillData = Pick<
    Bill,
    'title' | 'description' | 'amount' | 'payment_url' | 'due_date' | 'category_id' | 'is_recurring' | 'recurrence_period' | 'tags'
> & {
    id?: number;
};

interface Props {
    categories: Category[];
    tags: string[];
    bill: BillData;
    submitUrl: string;
    submitMethod: 'post' | 'put';
    options?: Omit<VisitOptions, 'data'>;
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
    payment_url: props.bill.payment_url,
    tags: props.bill.tags || [],
});

const formattedDate = computed((): string => {
    return formatDate(form.due_date?.toString() || '');
});

function updateDueDate(date?: DateValue): void {
    if (date) {
        form.due_date = date.toString();
    } else {
        form.due_date = undefined;
    }
}

const date = computed({
    get: () => {
        return form.due_date ? parseDate(format(form.due_date, 'yyyy-MM-dd')) : undefined;
    },
    set: (val) => val,
});

onMounted(() => {
    if (props.bill.due_date) {
        form.due_date = props.bill.due_date;
    } else {
        form.due_date = today(getLocalTimeZone()).toString();
    }
});

function submit(): void {
    form[props.submitMethod](props.submitUrl, {
        ...props.options,
        onSuccess: (data) => {
            if (!props.bill?.id) {
                form.reset();
            }
            if (props.options?.onSuccess) {
                props.options?.onSuccess(data);
            }
        },
    });
}
</script>

<template>
    <Form @submit="submit">
        <div class="grid gap-6">
            <!-- Title -->
            <FormField v-model="form.title" name="title">
                <FormItem>
                    <FormLabel>Title</FormLabel>
                    <FormControl>
                        <Input v-model="form.title" placeholder="Enter bill title" />
                    </FormControl>
                    <FormMessage :message="form.errors.title" />
                </FormItem>
            </FormField>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                <!-- Amount -->
                <FormField v-model="form.amount" name="amount">
                    <FormItem>
                        <FormLabel>Amount</FormLabel>
                        <FormControl>
                            <div class="relative">
                                <span class="text-muted-foreground absolute inset-y-0 left-0 flex items-center pl-3">{{
                                    $page.props?.team.current?.currency_symbol as string
                                }}</span>
                                <Input v-model="form.amount" type="number" min="0" step="1" placeholder="0.00" class="pl-8" />
                            </div>
                        </FormControl>
                        <FormMessage :message="form.errors.amount" />
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
                                        :class="!form.due_date ? 'text-muted-foreground' : ''"
                                    >
                                        <CalendarIcon class="mr-2 h-4 w-4" />
                                        {{ formattedDate || 'Select date' }}
                                    </Button>
                                </FormControl>
                            </PopoverTrigger>
                            <PopoverContent class="w-auto p-0" align="start">
                                <Calendar
                                    v-model="date"
                                    calendar-label="Due Date"
                                    initial-focus
                                    :min-value="today(getLocalTimeZone())"
                                    @update:model-value="updateDueDate"
                                />
                            </PopoverContent>
                        </Popover>
                        <FormMessage :message="form.errors.due_date" />
                    </FormItem>
                </FormField>

                <!-- Category -->
                <FormField v-model="form.category_id" name="category_id">
                    <FormItem>
                        <FormLabel>Category</FormLabel>
                        <Select v-model="form.category_id" class="w-full">
                            <FormControl class="w-full">
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
                        <FormMessage :message="form.errors.category_id" />
                    </FormItem>
                </FormField>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <!-- Payment URL -->
                <FormField v-model="form.payment_url" name="payment_url">
                    <FormItem>
                        <FormLabel>Payment URL</FormLabel>
                        <FormControl>
                            <Input type="url" v-model="form.payment_url" placeholder="Enter payment URL" />
                        </FormControl>
                        <FormMessage :message="form.errors.payment_url" />
                    </FormItem>
                </FormField>
                <!-- Tags -->
                <div>
                    <Label class="mb-2">Tags (Optional)</Label>
                    <TagInput
                        v-model="form.tags"
                        placeholder="Add tags (e.g. utilities, groceries)"
                        :options="
                            tags.map((item) => ({
                                value: item.toLowerCase(),
                                label: capitalize(item),
                            }))
                        "
                    />
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
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
                            <FormControl class="w-full">
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
                        <FormMessage :message="form.errors.recurrence_period" />
                    </FormItem>
                </FormField>
            </div>

            <!-- Description -->
            <FormField v-model="form.description" name="description">
                <FormItem>
                    <FormLabel>Description (Optional)</FormLabel>
                    <FormControl>
                        <Textarea v-model="form.description" placeholder="Add any additional details about this bill" rows="3" />
                    </FormControl>
                    <FormMessage :message="form.errors.description" />
                </FormItem>
            </FormField>

            <!-- Submit Button -->
            <Button type="submit" class="w-full" :disabled="form.processing">
                {{ props.bill.id ? 'Update Bill' : 'Add Bill' }}
            </Button>
        </div>
    </Form>
</template>
