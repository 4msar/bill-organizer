<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { type Webhook, type WebhookDelivery } from '@/types/model';
import { ChevronDownIcon, XIcon } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps<{
    open: boolean;
    webhook: Webhook | null;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

const isOpen = computed({
    get: () => props.open,
    set: (value) => emit('update:open', value),
});

const expandedDeliveryId = ref<number | null>(null);

const deliveries = computed(() => props.webhook?.deliveries ?? []);

function toggleExpanded(delivery: WebhookDelivery) {
    expandedDeliveryId.value = expandedDeliveryId.value === delivery.id ? null : delivery.id;
}

function isExpanded(deliveryId: number): boolean {
    return expandedDeliveryId.value === deliveryId;
}

function formatJson(obj: unknown): string {
    try {
        return JSON.stringify(obj, null, 2);
    } catch {
        return String(obj);
    }
}

function getStatusBadgeVariant(status: string): 'default' | 'secondary' | 'destructive' | 'outline' {
    if (status === 'delivered') return 'default';
    if (status === 'failed') return 'destructive';
    return 'secondary';
}

function getResponseStatusColor(statusCode: number | null): string {
    if (!statusCode) return 'text-muted-foreground';
    if (statusCode >= 200 && statusCode < 300) return 'text-green-600 dark:text-green-400';
    if (statusCode >= 400 && statusCode < 500) return 'text-yellow-600 dark:text-yellow-400';
    if (statusCode >= 500) return 'text-red-600 dark:text-red-400';
    return 'text-muted-foreground';
}

function formatDate(date: string | null): string {
    if (!date) return '—';
    return new Date(date).toLocaleString();
}
</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogContent class="max-h-[80vh] max-w-4/5 overflow-y-auto sm:max-w-3/4">
            <DialogHeader>
                <DialogTitle>Webhook Deliveries</DialogTitle>
                <DialogDescription v-if="webhook" class="text-sm">
                    Showing deliveries for <span class="text-foreground font-medium">{{ webhook.name }}</span>
                </DialogDescription>
            </DialogHeader>

            <div class="overflow-hidden rounded-md border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Event</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead>Response</TableHead>
                            <TableHead>Attempts</TableHead>
                            <TableHead>Delivered At</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <template v-for="delivery in deliveries" :key="delivery.id">
                            <TableRow class="hover:bg-muted/50 cursor-pointer" @click="toggleExpanded(delivery)">
                                <TableCell class="text-sm font-medium">
                                    <div class="flex items-center gap-2">
                                        <ChevronDownIcon :class="['h-4 w-4 transition-transform', isExpanded(delivery.id) ? 'rotate-180' : '']" />
                                        {{ delivery.event_name }}
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <Badge :variant="getStatusBadgeVariant(delivery.status)">
                                        {{ delivery.status }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <span :class="getResponseStatusColor(delivery.response_status)" class="font-mono text-sm">
                                        {{ delivery.response_status ?? '—' }}
                                    </span>
                                </TableCell>
                                <TableCell class="text-center">
                                    <Badge variant="outline">{{ delivery.attempts }}</Badge>
                                </TableCell>
                                <TableCell class="text-sm">
                                    {{ formatDate(delivery.delivered_at) }}
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="isExpanded(delivery.id)" class="hover:bg-transparent">
                                <TableCell colspan="5" class="bg-muted/30 p-4">
                                    <div class="space-y-4">
                                        <div v-if="delivery.payload" class="space-y-2">
                                            <h4 class="text-sm font-semibold">Payload</h4>
                                            <pre class="bg-background text-muted-foreground overflow-x-auto rounded border p-3 text-xs">{{
                                                formatJson(delivery.payload)
                                            }}</pre>
                                        </div>
                                        <div v-if="delivery.response_body" class="space-y-2">
                                            <h4 class="text-sm font-semibold">Response Body</h4>
                                            <pre class="bg-background text-muted-foreground max-w-full overflow-x-auto rounded border p-3 text-xs">{{
                                                formatJson(JSON.parse(delivery.response_body))
                                            }}</pre>
                                        </div>
                                        <div v-if="!delivery.payload && !delivery.response_body" class="text-muted-foreground text-sm italic">
                                            No details available
                                        </div>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </template>
                        <TableRow v-if="deliveries.length === 0">
                            <TableCell colspan="5" class="text-muted-foreground py-8 text-center"> No deliveries yet. </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <div class="flex justify-end">
                <Button variant="outline" @click="isOpen = false">
                    <XIcon class="mr-2 h-4 w-4" />
                    Close
                </Button>
            </div>
        </DialogContent>
    </Dialog>
</template>
