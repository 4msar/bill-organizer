<script setup lang="ts">
import BillForm, { BillData } from '@/components/bills/BillForm.vue';
import Details from '@/components/bills/Details.vue';
import HeadingSmall from '@/components/shared/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { CalendarEvent, EventCalendar } from '@/components/ui/event-calendar';
import AppLayout from '@/layouts/AppLayout.vue';
import { Bill, Category, SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Pen, ScanEye } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

const { props } = usePage<SharedData & {
    bills: Bill[]
    categories: Category[]
}>()

const selectedEvent = ref<CalendarEvent | null>(null)
const editSelectedEvent = ref<boolean>(false)
const selectedDate = ref<Date | null>(null)
const openNewEventDialog = ref(false)

const SevenDaysInMiliseconds = 7 * 24 * 60 * 60 * 1000;

const eventMaper = (item: Bill): CalendarEvent => ({
    id: item.id.toString(),
    title: item.title,
    description: item.description,
    start: new Date(item.due_date as string),
    end: new Date(item.due_date as string),
    // get random color
    color: getColorByItem(item),
    location: item.payment_url
})

const getColorByItem = (item: Bill) => {
    if (item.status === 'paid') return 'emerald'

    // if due date in 7 days
    if (new Date(item.due_date as string).getTime() - new Date().getTime() < SevenDaysInMiliseconds) {
        return 'orange'
    }

    return 'sky'
}

const bills = ref(props.bills)

watch(() => usePage<SharedData>().props.bills, (newBills) => {
    bills.value = (newBills as Bill[])
}, { deep: true });

const handleEventSelect = (event: CalendarEvent) => {
    selectedEvent.value = event
    openNewEventDialog.value = true
}

const handleNewEventClick = (date: Date) => {
    openNewEventDialog.value = true
    selectedDate.value = date
}

const bill = computed(() => bills.value.find(item => item.id.toString() === selectedEvent.value?.id?.toString()))

const billItemFormData = computed<BillData>(() => {

    return selectedEvent.value && bill.value ? {
        title: bill.value?.title,
        description: bill.value?.description,
        amount: bill.value?.amount,
        due_date: bill.value?.due_date,
        category_id: bill.value?.category_id,
        is_recurring: bill.value?.is_recurring,
        recurrence_period: bill.value?.recurrence_period,
        payment_url: bill.value?.payment_url,
        id: bill.value?.id,

    } : {
        title: '',
        description: '',
        amount: 0,
        due_date: selectedDate.value?.toDateString() ?? undefined,
        category_id: null,
        is_recurring: false,
        recurrence_period: null,
        payment_url: '',
    }
})

const handleDialogClose = () => {
    openNewEventDialog.value = false
    selectedDate.value = null
    selectedEvent.value = null
    editSelectedEvent.value = false
}
</script>

<template>
    <AppLayout :breadcrumbs="[
        {
            title: 'Calendar',
            href: route('calendar'),
        },
    ]">

        <Head title="Calendar" />

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <HeadingSmall title="Calendar" description="View and manage your bills." class="mb-4" />

                <EventCalendar :events="bills.map(eventMaper)" @new-event="handleNewEventClick"
                    @select-event="handleEventSelect" />

                <Dialog :open="Boolean(openNewEventDialog)" @update:open="handleDialogClose">
                    <DialogContent class="sm:max-w-2xl">
                        <DialogHeader>
                            <DialogTitle>{{ selectedEvent?.id ? 'Edit Bill' : 'Create Bill' }}</DialogTitle>
                            <DialogDescription>
                                Enter your bill details.
                            </DialogDescription>
                        </DialogHeader>

                        <template v-if="selectedEvent?.id && bill && !editSelectedEvent">
                            <Details :bill="bill">
                                <template #footer>
                                    <Button @click="editSelectedEvent = true">
                                        <Pen class="mr-1 size-4" />
                                        Edit
                                    </Button>
                                </template>
                            </Details>

                            <Link v-if="bill.status !== 'unpaid'" class="w-auto justify-self-end"
                                :href="route('bills.show', bill.id)">
                            <Button>
                                <ScanEye class="mr-1 size-4" />
                                Details
                            </Button>
                            </Link>
                        </template>

                        <BillForm v-else :categories="props.categories"
                            :submit-url="editSelectedEvent && bill ? route('bills.update', bill.id) : route('bills.store')"
                            :submit-method="editSelectedEvent && bill ? 'put' : 'post'" :options="{
                                onSuccess: () => handleDialogClose()
                            }" :bill="billItemFormData" />
                    </DialogContent>
                </Dialog>
            </div>
        </div>
    </AppLayout>
</template>
