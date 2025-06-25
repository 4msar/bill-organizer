<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';

import HeadingSmall from '@/components/shared/HeadingSmall.vue';
import TeamForm from '@/components/team/TeamForm.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AuthLayout.vue';


const form = useForm({
    name: '',
    description: '',
    icon: null,
    currency: 'USD',
    currency_symbol: '$',
});

const submit = () => {
    form.post(route('team.store'));
};
</script>

<template>
    <AppLayout>

        <Head title="Create Team" />

        <div class="flex flex-col space-y-6">
            <HeadingSmall title="Setup your Team information"
                description="Enter your team details to create your team." />

            <TeamForm :form="form" @submit="submit">
                <div class="flex items-center justify-between gap-4">
                    <Button :disabled="form.processing">Create</Button>
                    <span class="text-sm italic" v-if="$page.props.team.items.length < 1">
                        or visit your
                        <Link :href="route('profile.edit')">profile</Link>
                    </span>
                </div>
            </TeamForm>
        </div>
    </AppLayout>
</template>
