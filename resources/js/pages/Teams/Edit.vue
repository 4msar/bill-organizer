<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';

import HeadingSmall from '@/components/shared/HeadingSmall.vue';
import DeleteTeam from '@/components/team/DeleteTeam.vue';
import TeamForm from '@/components/team/TeamForm.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { SharedData, type BreadcrumbItem } from '@/types';

const { props } = usePage<SharedData>()

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Team settings',
        href: '/team/settings',
    },
];

const form = useForm<{
    name: string;
    description: string;
    icon: File | null;
    currency: string;
    currency_symbol: string;
    _method?: string;
}>({
    name: props.team.current.name,
    description: props.team.current.description,
    icon: null,
    currency: (props.team.current?.currency) || 'USD',
    currency_symbol: (props.team?.current?.currency_symbol) || '$',
    _method: "PUT"
});

const submit = () => {
    form.post(route('team.settings'));
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">

        <Head title="Team settings" />

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col space-y-6">
                    <HeadingSmall title="Team information" description="Update your team details." />

                    <TeamForm :form="form" @submit="submit">
                        <div class="flex items-center gap-4">
                            <Button :disabled="form.processing">Save</Button>
                        </div>
                    </TeamForm>
                </div>

                <hr class="my-6" />

                <DeleteTeam />
            </div>
        </div>
    </AppLayout>
</template>
