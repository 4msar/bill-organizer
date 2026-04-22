<script setup lang="ts">
import Confirm from '@/components/shared/Confirm.vue';
import HeadingSmall from '@/components/shared/HeadingSmall.vue';
import WebhookDeliveriesDialog from '@/components/team/WebhookDeliveriesDialog.vue';
import WebhookFormDialog from '@/components/team/WebhookFormDialog.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { type Webhook, type WebhookEvent } from '@/types/model';
import { HistoryIcon, PencilIcon, PlusIcon } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    webhooks: Webhook[];
    events: WebhookEvent[];
    methods: string[];
}>();

const showDialog = ref(false);
const editingWebhook = ref<Webhook | null>(null);
const showDeliveriesDialog = ref(false);
const selectedWebhookForDeliveries = ref<Webhook | null>(null);

function openCreate() {
    editingWebhook.value = null;
    showDialog.value = true;
}

function openEdit(webhook: Webhook) {
    editingWebhook.value = webhook;
    showDialog.value = true;
}

function openDeliveries(webhook: Webhook) {
    selectedWebhookForDeliveries.value = webhook;
    showDeliveriesDialog.value = true;
}

function eventLabel(value: string): string {
    return props.events.find((e) => e.value === value)?.label ?? value;
}
</script>

<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <HeadingSmall title="Webhooks" description="Manage outgoing webhooks triggered by team events." />
            <Button size="sm" variant="outline" @click="openCreate">
                <PlusIcon class="mr-1 h-4 w-4" />
                Add Webhook
            </Button>
        </div>

        <div class="rounded-md border">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>Name</TableHead>
                        <TableHead>URL</TableHead>
                        <TableHead>Method</TableHead>
                        <TableHead>Events</TableHead>
                        <TableHead>Status</TableHead>
                        <TableHead class="text-right">Actions</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="webhook in webhooks" :key="webhook.id">
                        <TableCell class="font-medium">{{ webhook.name }}</TableCell>
                        <TableCell class="max-w-48 truncate text-sm">
                            <span :title="webhook.url">{{ webhook.url }}</span>
                        </TableCell>
                        <TableCell>
                            <Badge variant="outline">{{ webhook.method }}</Badge>
                        </TableCell>
                        <TableCell>
                            <div class="flex flex-wrap gap-1">
                                <Badge v-for="event in webhook.events" :key="event" variant="secondary" class="text-xs">
                                    {{ eventLabel(event) }}
                                </Badge>
                            </div>
                        </TableCell>
                        <TableCell>
                            <Badge :variant="webhook.is_active ? 'default' : 'secondary'">
                                {{ webhook.is_active ? 'Active' : 'Inactive' }}
                            </Badge>
                        </TableCell>
                        <TableCell class="text-right">
                            <div class="flex items-center justify-end gap-2">
                                <Button
                                    size="icon"
                                    variant="ghost"
                                    @click="openDeliveries(webhook)"
                                    aria-label="View deliveries"
                                    title="View deliveries"
                                >
                                    <HistoryIcon class="h-4 w-4" />
                                </Button>
                                <Button size="icon" variant="ghost" @click="openEdit(webhook)" aria-label="Edit webhook">
                                    <PencilIcon class="h-4 w-4" />
                                </Button>
                                <Confirm
                                    :url="route('webhooks.destroy', webhook.id)"
                                    title="Delete this webhook?"
                                    description="This action cannot be undone."
                                    modal
                                >
                                    <Button size="icon" variant="ghost" class="text-destructive hover:text-destructive" aria-label="Delete webhook">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-4 w-4"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                        >
                                            <path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6" />
                                        </svg>
                                    </Button>
                                </Confirm>
                            </div>
                        </TableCell>
                    </TableRow>
                    <TableRow v-if="webhooks.length === 0">
                        <TableCell colspan="6" class="text-muted-foreground py-8 text-center"> No webhooks configured yet. </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <WebhookFormDialog v-model:open="showDialog" :webhook="editingWebhook" :events="events" :methods="methods" />

        <WebhookDeliveriesDialog v-model:open="showDeliveriesDialog" :webhook="selectedWebhookForDeliveries" />
    </div>
</template>
