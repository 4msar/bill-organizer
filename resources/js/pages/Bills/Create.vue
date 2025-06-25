<script setup lang="ts">
import BillForm from '@/components/bills/BillForm.vue';
import HeadingSmall from '@/components/shared/HeadingSmall.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { Category } from '@/types/model';
import { Head } from '@inertiajs/vue3';

interface Props {
    categories: Category[];
}

defineProps<Props>();
</script>

<template>
    <AppLayout :breadcrumbs="[
        {
            title: 'Bills',
            href: route('bills.index'),
        },
        {
            title: 'Add New Bill',
            href: route('bills.create'),
        },
    ]">

        <Head title="Add New Bill" />

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <HeadingSmall title="Add new bill" description="Enter your bill details." class="mb-4" />

                <BillForm :categories="categories" :bill="{
                    title: '',
                    description: '',
                    amount: 0,
                    due_date: undefined,
                    category_id: null,
                    is_recurring: false,
                    recurrence_period: null,
                }" :submit-url="route('bills.store')" submit-method="post" />

            </div>
        </div>
    </AppLayout>
</template>
