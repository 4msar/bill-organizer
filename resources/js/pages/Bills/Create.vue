<script setup lang="ts">
import BillForm from '@/components/bills/BillForm.vue';
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
    <AppLayout
        :breadcrumbs="[
            {
                title: 'Bills',
                href: route('bills.index'),
            },
            {
                title: 'Add New Bill',
                href: route('bills.create'),
            },
        ]"
    >
        <Head title="Add New Bill" />

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="mb-6">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Add New Bill</h2>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>New Bill Details</CardTitle>
                        <CardDescription> Enter the details of your new bill or subscription. </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <BillForm
                            :categories="categories"
                            :bill="{
                                title: '',
                                description: null,
                                amount: '',
                                due_date: null,
                                category_id: null,
                                is_recurring: false,
                                recurrence_period: null,
                            }"
                            :submit-url="route('bills.store')"
                            submit-method="post"
                        />
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
