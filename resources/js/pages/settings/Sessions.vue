<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

import HeadingSmall from '@/components/shared/HeadingSmall.vue';
import { type BreadcrumbItem } from '@/types';

import Tooltip from '@/components/shared/Tooltip.vue';
import { Button, buttonVariants } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type SharedData } from '@/types';
import { Check, Copy, KeyRound, Trash } from 'lucide-vue-next';

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

const isCreateTokenDialogOpen = ref(false);
const createdToken = ref<string | null>(null);
const tokenCopied = ref(false);
const page = usePage<SharedData>();
const form = useForm({
    name: '',
    expires_at: '',
});

function createApiToken() {
    form.post(route('profile.sessions.tokens.create'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            createdToken.value = page.props.flash.token;
            tokenCopied.value = false;
        },
    });
}

async function copyToken() {
    if (!createdToken.value) {
        return;
    }

    await navigator.clipboard.writeText(createdToken.value);
    tokenCopied.value = true;
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Sessions" />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall title="Sessions" description="Manage your active login sessions" class="items-start justify-between">
                    <div class="flex items-center gap-2">
                        <Dialog v-model:open="isCreateTokenDialogOpen">
                            <DialogTrigger as-child>
                                <Button size="sm">
                                    <KeyRound class="h-4 w-4" />
                                    Create API token
                                </Button>
                            </DialogTrigger>
                            <DialogContent class="sm:max-w-[425px]">
                                <DialogHeader>
                                    <DialogTitle>Create API token</DialogTitle>
                                    <DialogDescription>Give the token a name and optionally set an expiration date.</DialogDescription>
                                </DialogHeader>

                                <div v-if="createdToken" class="grid gap-3 py-4">
                                    <p class="text-sm font-medium">Your API token</p>
                                    <div class="flex gap-2">
                                        <Input :model-value="createdToken" readonly class="font-mono text-xs" />
                                        <Button type="button" variant="outline" size="icon" @click="copyToken">
                                            <Check v-if="tokenCopied" class="h-4 w-4" />
                                            <Copy v-else class="h-4 w-4" />
                                            <span class="sr-only">{{ tokenCopied ? 'Token copied' : 'Copy token' }}</span>
                                        </Button>
                                    </div>
                                    <p class="text-muted-foreground text-sm">Copy this token now. It will not be shown again.</p>
                                </div>

                                <form v-else class="grid gap-4 py-4" @submit.prevent="createApiToken">
                                    <div class="grid gap-2">
                                        <Label for="token-name">Name</Label>
                                        <Input id="token-name" v-model="form.name" required autocomplete="off" placeholder="My device" />
                                        <p v-if="form.errors.name" class="text-destructive text-sm">{{ form.errors.name }}</p>
                                    </div>

                                    <div class="grid gap-2">
                                        <Label for="token-expires-at">Expire at <span class="text-muted-foreground">(optional)</span></Label>
                                        <Input id="token-expires-at" v-model="form.expires_at" type="datetime-local" />
                                        <p v-if="form.errors.expires_at" class="text-destructive text-sm">{{ form.errors.expires_at }}</p>
                                    </div>

                                    <DialogFooter>
                                        <Button type="submit" :disabled="form.processing">Create token</Button>
                                    </DialogFooter>
                                </form>
                            </DialogContent>
                        </Dialog>

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
                    </div>
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
