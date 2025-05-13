<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';

import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { SharedData, type BreadcrumbItem } from '@/types';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Application Settings',
        href: '/settings/application',
    },
];

const {
    props: {
        auth: { user },
    },
} = usePage<SharedData>();

const form = useForm({
    currency: (user.metas.currency as string) || 'USD',
    currency_symbol: (user.metas.currency_symbol as string) || '$',
});

const submit = () => {
    form.put(route('application.update'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Application settings" />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall title="Application specific settings" description="User specific application preferences." />

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="currency">Currency</Label>
                        <Input
                            id="currency"
                            class="mt-1 block w-full"
                            v-model="form.currency"
                            required
                            autocomplete="currency"
                            placeholder="Currency Code. Ex: USD"
                        />
                        <InputError class="mt-2" :message="form.errors.currency" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="currency_symbol">Currency Symbol</Label>
                        <Input
                            id="currency_symbol"
                            class="mt-1 block w-full"
                            v-model="form.currency_symbol"
                            required
                            autocomplete="currency_symbol"
                            placeholder="Currency Code. Ex: $"
                        />
                        <InputError class="mt-2" :message="form.errors.currency_symbol" />
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
