<script setup lang="ts">
import InputError from '@/components/shared/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { getCurrencyFullName } from '@/lib/utils';
import { useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '../ui/select';

const { method, submitUrl, defaultValues } = defineProps<{
    submitUrl: string;
    method?: 'POST' | 'PUT';
    defaultValues: {
        name: string;
        description: string;
        icon: File | null;
        currency: string;
        currency_symbol: string;
    };
}>();
const currencies = ref<string[]>(Intl.supportedValuesOf('currency'));

const form = useForm<{
    name: string;
    description: string;
    icon: File | null;
    currency: string;
    currency_symbol: string;
    _method?: 'POST' | 'PUT';
}>({
    ...defaultValues,
    _method: method,
});

watch(
    () => form.currency,
    (newCurrency) => {
        try {
            const formatter = new Intl.NumberFormat('en', {
                currency: newCurrency,
                maximumFractionDigits: 1,
                style: 'currency',
                currencyDisplay: 'narrowSymbol',
            });
            const parts = formatter.formatToParts(1000);
            const symbolPart = parts.find((part) => part.type === 'currency');
            if (symbolPart) {
                form.currency_symbol = symbolPart.value;
            }
            // eslint-disable-next-line @typescript-eslint/no-unused-vars
        } catch (error) {
            // Invalid currency code
        }
    },
    { immediate: true },
);

const submit = () => {
    if (method === 'PUT') {
        form._method = 'PUT';
    }

    form.post(submitUrl);
};
</script>

<template>
    <form @submit.prevent="submit" class="space-y-6">
        <div class="grid gap-2">
            <Label for="name">Name</Label>
            <Input id="name" class="mt-1 block w-full" v-model="form.name" required autocomplete="name" placeholder="Team name" />
            <InputError class="mt-2" :message="form.errors.name" />
        </div>

        <div class="grid gap-2">
            <Label for="description">Description</Label>
            <Textarea
                id="description"
                type="description"
                class="mt-1 block w-full"
                v-model="form.description"
                placeholder="Write something about your team..."
            />
            <InputError class="mt-2" :message="form.errors.description" />
        </div>

        <div class="grid gap-2">
            <Label for="icon">Icon / Logo</Label>
            <Input id="icon" type="file" accept="image/*" class="mt-1 block w-full" @change="form.icon = $event?.target?.files[0]" />
            <InputError class="mt-2" :message="form.errors.icon" />
        </div>

        <div class="grid gap-2">
            <Label for="currency">Currency</Label>
            <Select v-model="form.currency" class="w-full">
                <SelectTrigger class="w-full">
                    <SelectValue placeholder="Select a category" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem v-for="currency in currencies" :key="currency" :value="currency">
                        {{ currency }} - {{ getCurrencyFullName(currency) }}
                    </SelectItem>
                </SelectContent>
            </Select>
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

        <slot :form="form" />
    </form>
</template>
