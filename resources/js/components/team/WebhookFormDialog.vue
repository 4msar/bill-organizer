<script setup lang="ts">
import InputError from '@/components/shared/InputError.vue';
import MultiSelect from '@/components/shared/MultiSelect.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { type Webhook, type WebhookEvent, type WebhookMethod } from '@/types/model';
import { useForm } from '@inertiajs/vue3';
import { watchEffect } from 'vue';

const props = defineProps<{
    open: boolean;
    webhook?: Webhook | null;
    events: WebhookEvent[];
    methods: string[];
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

const form = useForm<{
    name: string;
    url: string;
    method: WebhookMethod;
    events: string[];
    is_active: boolean;
}>({
    name: '',
    url: '',
    method: 'POST' as WebhookMethod,
    events: [] as string[],
    is_active: true,
});

watchEffect(() => {
    if (props.webhook) {
        form.name = props.webhook.name;
        form.url = props.webhook.url;
        form.method = props.webhook.method ?? 'POST';
        form.events = [...(props.webhook.events ?? [])];
        form.is_active = props.webhook.is_active;
    } else {
        form.name = '';
        form.url = '';
        form.method = 'POST';
        form.events = [];
        form.is_active = true;
    }
});

function handleClose(value: boolean) {
    if (!value) {
        form.reset();
        form.clearErrors();
    }
    emit('update:open', value);
}

function submit() {
    if (props.webhook) {
        form.put(route('webhooks.update', props.webhook.id), {
            preserveScroll: true,
            onSuccess: () => handleClose(false),
            preserveState: false,
        });
    } else {
        form.post(route('webhooks.store'), {
            preserveScroll: true,
            onSuccess: () => handleClose(false),
            preserveState: false,
        });
    }
}

const eventOptions = props.events.map((e) => ({ label: e.label, value: e.value }));
</script>

<template>
    <Dialog :open="open" @update:open="handleClose">
        <DialogContent class="sm:max-w-[520px]">
            <DialogHeader>
                <DialogTitle>{{ webhook ? 'Edit Webhook' : 'Add Webhook' }}</DialogTitle>
                <DialogDescription>
                    {{ webhook ? 'Update the webhook configuration.' : 'Configure a new webhook for your team.' }}
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submit" class="space-y-4 py-2">
                <div class="grid gap-2">
                    <Label for="webhook-name">Name</Label>
                    <Input id="webhook-name" v-model="form.name" placeholder="My Webhook" />
                    <InputError :message="form.errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="webhook-url">URL</Label>
                    <Input id="webhook-url" v-model="form.url" placeholder="https://example.com/webhook" type="url" />
                    <InputError :message="form.errors.url" />
                </div>

                <div class="grid gap-2">
                    <Label for="webhook-method">HTTP Method</Label>
                    <Select v-model="form.method">
                        <SelectTrigger id="webhook-method" class="w-full">
                            <SelectValue placeholder="Select method" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="m in methods" :key="m" :value="m">{{ m }}</SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.method" />
                </div>

                <div class="grid gap-2">
                    <Label>Events</Label>
                    <MultiSelect :options="eventOptions" v-model="form.events" placeholder="Select events..." :searchable="true" />
                    <InputError :message="form.errors.events" />
                </div>

                <div class="flex items-center gap-3">
                    <Switch id="webhook-active" v-model:checked="form.is_active" :model-value="form.is_active" />
                    <Label for="webhook-active">Active</Label>
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="handleClose(false)">Cancel</Button>
                    <Button type="submit" :disabled="form.processing">
                        {{ webhook ? 'Update' : 'Create' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
