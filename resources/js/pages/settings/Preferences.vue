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
        team: { items: teams },
    },
} = usePage<SharedData>();

interface SettingsForm {
    email_notification: boolean;
    web_notification: boolean;
    early_reminder_days: (number | string)[];
    excluded_notification_teams: number[];
    [key: string]: any;
}

const form = useForm<SettingsForm>({
    email_notification: Boolean(user.metas?.email_notification ?? true),
    web_notification: Boolean(user.metas?.web_notification ?? true),
    enable_reports: Boolean(user.metas?.enable_reports ?? false),
    notes_view_mode: (user.metas?.notes_view_mode as string) ?? 'grid',
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
    excluded_notification_teams: user.metas?.excluded_notification_teams ? [...((user.metas.excluded_notification_teams as number[]) ?? [])] : [],
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
                            <SelectTrigger class="!h-auto w-full text-left whitespace-normal">
                                <SelectValue placeholder="Select" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="(label, value) in days" :key="value" :value="value">
                                    <span>{{ label }}</span>
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="grid gap-2">
                        <Label for="excluded-teams" class="flex items-center space-x-3"> Exclude Teams from Notifications </Label>
                        <p class="text-muted-foreground text-sm">
                            Select teams for which you do not want to receive notifications. By default, all teams are enabled.
                        </p>
                        <Select v-model="form.excluded_notification_teams" multiple>
                            <SelectTrigger class="!h-auto w-full text-left whitespace-normal">
                                <SelectValue placeholder="All teams enabled" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="team in teams" :key="team.id" :value="team.id">
                                    <span>{{ team.name }}</span>
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <hr />

                    <HeadingSmall title="Feature Settings" description="Enable or disabled some experimental features." />

                    <div class="flex flex-row items-center justify-between rounded-lg border p-4">
                        <div class="space-y-0.5">
                            <Label class="text-base"> Reports </Label>
                            <p class="text-sm">
                                Enable the reports feature to view reports and analytics. This is an experimental feature and may not be fully
                                functional.
                            </p>
                        </div>
                        <div>
                            <Switch :model-value="form.enable_reports" @update:model-value="(value) => (form.enable_reports = value)" />
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label for="notes-view" class="text-base"> Notes View Mode </Label>
                        <p class="text-muted-foreground text-sm">
                            Choose how you want to view your notes. Grid view shows notes as cards, while sidebar view displays them in a list with a
                            detail panel.
                        </p>
                        <Select v-model="form.notes_view_mode">
                            <SelectTrigger class="w-full">
                                <SelectValue placeholder="Select view mode" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="grid">Grid View</SelectItem>
                                <SelectItem value="sidebar">Sidebar View</SelectItem>
                            </SelectContent>
                        </Select>
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
