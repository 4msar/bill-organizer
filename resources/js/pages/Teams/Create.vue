<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

import Impersonate from '@/components/pages/Impersonate.vue';
import HeadingSmall from '@/components/shared/HeadingSmall.vue';
import TeamForm from '@/components/team/TeamForm.vue';
import { Button } from '@/components/ui/button';
import { TooltipProvider } from '@/components/ui/tooltip';
import AppLayout from '@/layouts/AuthLayout.vue';
</script>

<template>
    <AppLayout>
        <Head title="Create Team" />

        <div class="flex flex-col space-y-6">
            <HeadingSmall title="Setup your Team information" description="Enter your team details to create your team." />

            <TooltipProvider :delay-duration="0">
                <TeamForm
                    :submit-url="route('team.store')"
                    :default-values="{
                        name: '',
                        slug: '',
                        description: '',
                        icon: null,
                        currency: 'USD',
                        currency_symbol: '$',
                    }"
                >
                    <template v-slot="{ form }">
                        <div class="flex items-center justify-between gap-4">
                            <Button :disabled="form.processing">Create</Button>
                            <span class="text-sm italic" v-if="$page.props.team.items.length < 1">
                                or visit your
                                <Link :href="route('profile.edit')">profile</Link>
                            </span>
                        </div>
                    </template>
                </TeamForm>
            </TooltipProvider>
        </div>

        <Impersonate />
    </AppLayout>
</template>
