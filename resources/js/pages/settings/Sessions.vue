<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

import HeadingSmall from '@/components/shared/HeadingSmall.vue';
import { type BreadcrumbItem } from '@/types';

import Tooltip from '@/components/shared/Tooltip.vue';
import { buttonVariants } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { Trash } from 'lucide-vue-next';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Sessions',
        href: '/settings/sessions',
    },
];

const { webSessions, apiSessions } = defineProps<{
    webSessions: {
        id: number;
        ip_address: string;
        user_agent: string;
        is_current: boolean;
        last_activity: string;
    }[];
    apiSessions: {
        id: number;
        name: string;
        expiry: string;
        last_activity: string;
    }[];
}>();
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Sessions" />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall title="Sessions" description="Manage your active login sessions" class="items-start justify-between">
                    <Tooltip title="Logout from all sessions/tokens.">
                        <Link
                            :href="route('profile.sessions.revoke')"
                            method="delete"
                            :data="{ clear_all: 'true' }"
                            :class="buttonVariants({ variant: 'destructive', size: 'sm' })"
                        >
                            <Trash class="inline h-4 w-4" />
                        </Link>
                    </Tooltip>
                </HeadingSmall>

                <div class="space-y-10">
                    <!-- WEB SESSIONS -->
                    <section>
                        <h2 class="mb-4 text-xl font-semibold">Web Sessions</h2>

                        <div v-if="!webSessions.length" class="text-gray-500">No active web sessions</div>

                        <ul class="space-y-3">
                            <li v-for="session in webSessions" :key="session.id" class="rounded border p-4">
                                <div class="flex justify-between">
                                    <div>
                                        <p class="font-medium">
                                            {{ session.ip_address }}
                                            <span v-if="session.is_current" class="text-sm text-green-600"> (Current) </span>
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            {{ session.user_agent }}
                                        </p>
                                        <p class="text-sm text-gray-400">Active {{ session.last_activity }}</p>
                                    </div>

                                    <div v-if="!session.is_current">
                                        <Tooltip title="Revoke or Logout">
                                            <Link
                                                :href="route('profile.sessions.revoke')"
                                                method="delete"
                                                :data="{ session_id: session.id, session_type: 'web' }"
                                                :class="buttonVariants({ variant: 'destructive', size: 'sm' })"
                                            >
                                                <Trash class="inline h-4 w-4" />
                                            </Link>
                                        </Tooltip>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </section>

                    <!-- API SESSIONS -->
                    <section>
                        <h2 class="mb-4 text-xl font-semibold">API / Device Sessions</h2>

                        <div v-if="!apiSessions.length" class="text-gray-500">No API tokens</div>

                        <ul class="space-y-3">
                            <li v-for="token in apiSessions" :key="token.id" class="flex justify-between rounded border p-4">
                                <div>
                                    <p class="font-medium">{{ token.name }}</p>
                                    <p class="text-sm text-gray-500">Expires: {{ token.expiry }}</p>
                                    <p class="text-sm text-gray-500">Last used: {{ token.last_activity }}</p>
                                </div>

                                <div>
                                    <Tooltip title="Revoke or Logout">
                                        <Link
                                            :href="route('profile.sessions.revoke')"
                                            method="delete"
                                            :data="{ session_id: token.id, session_type: 'api' }"
                                            :class="buttonVariants({ variant: 'destructive', size: 'sm' })"
                                        >
                                            <Trash class="inline h-4 w-4" />
                                        </Link>
                                    </Tooltip>
                                </div>
                            </li>
                        </ul>
                    </section>
                </div>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
