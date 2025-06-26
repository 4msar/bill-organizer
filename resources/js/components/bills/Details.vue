<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { cn, formatCurrency, formatDate } from '@/lib/utils';
import { Bill } from '@/types/model';
import { CalendarIcon, Clock, Link2, Receipt, RotateCcw } from 'lucide-vue-next';
import { computed } from 'vue';

interface Props {
    bill: Bill;
}

const { bill } = defineProps<Props>();

const isPastDue = computed((): boolean => {
    return new Date(bill.due_date as string) < new Date() && bill.status === 'unpaid';
});

</script>

<template>
    <!-- Bill Details Card -->
    <Card>
        <CardHeader>
            <div class="flex items-start justify-between">
                <div>
                    <CardTitle class="flex items-center gap-2">
                        {{ bill.title }}
                        <Badge v-if="bill.is_recurring" variant="outline">Recurring</Badge>
                        <Badge :variant="bill.status === 'paid' ? 'secondary' : 'default'">
                            {{ bill.status === 'paid' ? 'Paid' : 'Unpaid' }}
                        </Badge>
                    </CardTitle>
                    <CardDescription v-if="bill.category"> Category: {{ bill.category.name }} </CardDescription>
                </div>
                <div class="text-2xl font-bold">
                    {{ formatCurrency(bill.amount, $page.props?.team?.current?.currency as string) }}
                </div>
            </div>
        </CardHeader>

        <CardContent>
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <h3 class="text-muted-foreground mb-1 text-sm font-medium">Due Date</h3>
                    <p class="flex items-center" :class="{ 'text-destructive': isPastDue }">
                        <CalendarIcon class="mr-2 h-4 w-4" />
                        {{ formatDate(bill.due_date as string) }}
                    </p>
                </div>

                <div v-if="bill.is_recurring">
                    <h3 class="text-muted-foreground mb-1 text-sm font-medium">Recurrence</h3>
                    <p class="flex items-center">
                        <RotateCcw class="mr-2 h-4 w-4" />
                        {{
                            bill.recurrence_period ? bill.recurrence_period.charAt(0).toUpperCase() +
                                bill.recurrence_period.slice(1) : ''
                        }}
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
        </CardContent>

        <CardFooter v-if="bill.status === 'unpaid'"
            :class="cn('flex', bill.payment_url ? 'justify-between' : 'justify-end')">
            <a v-if="bill.payment_url" :href="bill.payment_url" target="_blank" rel="noopener noreferrer">
                <Button variant="secondary">
                    <Link2 class="mr-2 h-4 w-4" />
                    Go to Payment Page
                </Button>
            </a>
            <slot name="footer" />
        </CardFooter>
    </Card>
</template>