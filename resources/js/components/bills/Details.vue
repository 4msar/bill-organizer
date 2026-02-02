<script setup lang="ts">
import { Badge, BadgeVariants } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { cn, formatCurrency, formatDate, getVariantByStatus } from '@/lib/utils';
import { Bill } from '@/types/model';
import { CalendarIcon, Clock, Link2, RotateCcw } from 'lucide-vue-next';
import { capitalize, computed } from 'vue';

interface Props {
    bill: Bill;
}

const { bill } = defineProps<Props>();

const isPastDue = computed((): boolean => {
    return new Date(bill.due_date as string) < new Date() && (bill.status === 'unpaid' || bill.status === 'overdue');
});
</script>

<template>
    <!-- Bill Details Card -->
    <Card class="gap-3">
        <CardHeader>
            <div class="flex flex-col items-start justify-between space-y-2 space-x-2 sm:flex-row">
                <div class="space-y-2">
                    <CardTitle class="text-lg font-semibold">
                        {{ bill.title }}
                    </CardTitle>
                    <CardDescription>
                        <span class="flex w-full flex-wrap items-center gap-2">
                            <Badge v-if="bill.is_recurring" variant="outline">Recurring</Badge>
                            <Badge v-if="bill.has_trial" variant="outline" class="border-blue-200 bg-blue-50 text-blue-700"> Trial</Badge>
                            <Badge :variant="getVariantByStatus<BadgeVariants['variant']>(bill.status)">
                                {{ capitalize(bill.status) }}
                            </Badge>
                        </span>

                        <span class="mt-2 block" v-if="bill.category">Category: {{ bill.category.name }}</span>
                    </CardDescription>
                </div>
                <div class="text-2xl font-bold">
                    {{ formatCurrency(bill.amount, $page.props?.team?.current?.currency as string) }}
                </div>
            </div>
        </CardHeader>

        <CardContent>
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div class="dark:text-foreground rounded-md border border-neutral-700 p-2 text-neutral-700">
                    <h3 class="mb-1 text-sm font-medium">Due Date</h3>
                    <p class="flex items-center" :class="{ 'text-destructive': isPastDue }">
                        <CalendarIcon class="mr-2 h-4 w-4" />
                        {{ formatDate(bill.due_date as string) }}
                    </p>
                </div>

                <div
                    v-if="bill.has_trial && bill.trial_start_date && bill.trial_end_date"
                    class="rounded-md border border-blue-700 p-2 text-blue-700"
                >
                    <h3 class="mb-1 text-sm font-medium">Trial Period</h3>
                    <p class="flex items-center text-sm">
                        <CalendarIcon class="mr-2 h-4 w-4" />
                        {{ formatDate(bill.trial_start_date) }} - {{ formatDate(bill.trial_end_date) }}
                    </p>
                </div>

                <div v-if="bill.is_recurring">
                    <h3 class="text-muted-foreground mb-1 text-sm font-medium">Recurrence</h3>
                    <p class="flex items-center">
                        <RotateCcw class="mr-2 h-4 w-4" />
                        {{ bill.recurrence_period ? bill.recurrence_period.charAt(0).toUpperCase() + bill.recurrence_period.slice(1) : '' }}
                    </p>
                </div>

                <div v-if="bill.created_at">
                    <h3 class="text-muted-foreground mb-1 text-sm font-medium">Created</h3>
                    <p class="flex items-center">
                        <Clock class="mr-2 h-4 w-4" />
                        {{ formatDate(bill.created_at) }}
                    </p>
                </div>
            </div>

            <div v-if="bill.description" class="mt-6">
                <h3 class="text-muted-foreground mb-1 text-sm font-medium">Description</h3>
                <p class="text-sm">{{ bill.description }}</p>
            </div>

            <div v-if="bill.tags && bill.tags.length" class="mt-6">
                <div class="flex flex-wrap gap-2">
                    <Badge v-for="tag in bill.tags" :key="tag" variant="secondary" class="cursor-default">
                        {{ capitalize(tag) }}
                    </Badge>
                </div>
            </div>
        </CardContent>

        <CardFooter :class="cn('flex flex-wrap items-center gap-2', bill.payment_url ? 'justify-between' : 'justify-end')">
            <a v-if="bill.status !== 'paid' && bill.payment_url" :href="bill.payment_url" target="_blank" rel="noopener noreferrer">
                <Button variant="secondary">
                    <Link2 class="mr-2 h-4 w-4" />
                    Go to Payment Page
                </Button>
            </a>
            <slot name="footer" />
        </CardFooter>
    </Card>
</template>
