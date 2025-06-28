<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';

import HeadingSmall from '@/components/shared/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { SharedData, type BreadcrumbItem } from '@/types';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Application Preferences',
        href: '/settings/preferences',
    },
];

const days = {
    due_day: 'Same day',
    1: '1 day before due date',
    3: '3 days before due date',
    7: '7 days before due date',
    15: '15 days before due date',
    30: '30 days before due date',
};

const {
    props: {
        auth: { user },
    },
} = usePage<SharedData>();

interface NotificationSettings {
    email_notification: boolean;
    web_notification: boolean;
    early_reminder_days: (number | string)[];
    enable_notes: boolean;
    enable_calendar: boolean;
    [key: string]: any;
}

const form = useForm<NotificationSettings>({
    email_notification: Boolean(user.metas?.email_notification ?? true),
    web_notification: Boolean(user.metas?.web_notification ?? true),
    enable_notes: Boolean(user.metas?.enable_notes ?? false),
    enable_calendar: Boolean(user.metas?.enable_calendar ?? true),

    /**
     * early reminder days
     *
     * 1 day before due date
     * 3 days before due date
     * 7 days before due date
     * 15 days before due date
     * 30 days before due date
     */
    early_reminder_days: user.metas?.early_reminder_days ? [...((user.metas.early_reminder_days as number[]) ?? [])] : [],
});

const submit = () => {
    form.put(route('notifications.update'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Notification settings" />

        <SettingsLayout>
            <div class="space-y-6">
                <form @submit.prevent="submit" class="space-y-6">
                    <HeadingSmall title="Notification settings" description="User notification preferences." />
                    <div class="flex items-center justify-between">
                        <Label for="email" class="flex items-center space-x-3">
                            <Checkbox id="email" v-model="form.email_notification" />
                            <span>Email Notification</span>
                        </Label>
                    </div>
                    <div class="flex items-center justify-between">
                        <Label for="web" class="flex items-center space-x-3">
                            <Checkbox id="web" v-model="form.web_notification" />
                            <span>Web Notification</span>
                        </Label>
                    </div>

                    <div class="grid gap-2">
                        <Label for="reminder" class="flex items-center space-x-3"> Notification Reminder </Label>
                        <Select v-model="form.early_reminder_days" multiple>
                            <SelectTrigger class="w-full">
                                <SelectValue placeholder="Select" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="(label, value) in days" :key="value" :value="value">
                                    <span>{{ label }}</span>
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <hr />

                    <HeadingSmall title="Feature Settings" description="Enable or disabled some experimental features." />

                    <div class="flex flex-row items-center justify-between rounded-lg border p-4">
                        <div class="space-y-0.5">
                            <Label class="text-base"> Calendar </Label>
                            <p class="text-sm">Enable the Calendar feature to view bills and reminders in a calendar format.</p>
                        </div>
                        <div>
                            <Switch :model-value="form.enable_calendar" @update:model-value="(value) => (form.enable_calendar = value)" />
                        </div>
                    </div>
                    <div class="flex flex-row items-center justify-between rounded-lg border p-4">
                        <div class="space-y-0.5">
                            <Label class="text-base"> Notes </Label>
                            <p class="text-sm">
                                Enable the notes feature to add notes and linked to bills. This is an experimental feature and may not be fully
                                functional.
                            </p>
                        </div>
                        <div>
                            <Switch :model-value="form.enable_notes" @update:model-value="(value) => (form.enable_notes = value)" />
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <Button :disabled="form.processing">Save</Button>

                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p v-show="form.recentlySuccessful" class="text-sm text-neutral-600">Saved.</p>
                        </Transition>
                    </div>
                </form>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
