<script setup lang="ts">
import BillForm from '@/components/bills/BillForm.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { Bill, Category } from '@/types/model';
import { Head } from '@inertiajs/vue3';

interface Props {
    bill: Bill;
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
                title: `Edit Bill (${bill.title})`,
                href: route('bills.edit', bill.id),
            },
        ]"
    >
        <Head title="Edit Bill" />

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="mb-6">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Edit Bill</h2>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Edit Bill: {{ bill.title }}</CardTitle>
                        <CardDescription> Update the details of your bill. </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <BillForm :bill="bill" :categories="categories" :submit-url="route('bills.update', bill.id)" submit-method="put" />
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
