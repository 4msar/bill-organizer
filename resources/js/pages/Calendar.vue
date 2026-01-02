<script setup lang="ts">
import BillForm, { BillData } from '@/components/bills/BillForm.vue';
import Details from '@/components/bills/Details.vue';
import HeadingSmall from '@/components/shared/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { CalendarEvent, EventCalendar } from '@/components/ui/event-calendar';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { useEvents } from '@/composables/useEvents';
import AppLayout from '@/layouts/AppLayout.vue';
import { Bill, Category, SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Ban, Pen, ScanEye } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const { props } = usePage<
    SharedData & {
        bills: Bill[];
        categories: Category[];
        tags: string[];
    }
>();

const selectedEvent = ref<CalendarEvent | null>(null);
const editSelectedEvent = ref<boolean>(false);
const selectedDate = ref<Date | null>(null);
const openNewEventDialog = ref(false);
const { bills, events } = useEvents();

const handleEventSelect = (event: CalendarEvent) => {
    selectedEvent.value = event;
    openNewEventDialog.value = true;
};

const handleNewEventClick = (date: Date) => {
    openNewEventDialog.value = true;
    selectedDate.value = date;
};

const bill = computed(() => bills.value.find((item) => item.id.toString() === selectedEvent.value?.id?.toString()));

const billItemFormData = computed<Partial<BillData>>(() => {
    return selectedEvent.value && bill.value
        ? {
              title: bill.value?.title,
              description: bill.value?.description,
              amount: bill.value?.amount,
              due_date: bill.value?.due_date,
              category_id: bill.value?.category_id,
              is_recurring: bill.value?.is_recurring,
              recurrence_period: bill.value?.recurrence_period,
              payment_url: bill.value?.payment_url,
              id: bill.value?.id,
              tags: bill.value?.tags ?? [],
          }
        : {
              title: '',
              description: '',
              amount: 0,
              due_date: selectedDate.value?.toDateString() ?? undefined,
              category_id: null,
              is_recurring: false,
              recurrence_period: null,
              payment_url: '',
              tags: [],
          };
});

const handleDialogClose = () => {
    openNewEventDialog.value = false;
    selectedDate.value = null;
    selectedEvent.value = null;
    editSelectedEvent.value = false;
};
</script>

<template>
    <AppLayout
        :breadcrumbs="[
            {
                title: 'Calendar',
                href: route('calendar'),
            },
        ]"
    >
        <Head title="Calendar" />

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <HeadingSmall title="Calendar" description="View and manage your bills." class="mb-4 justify-between">
                    <Popover>
                        <PopoverTrigger as-child>
                            <svg
                                width="24"
                                height="24"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                class="lucide lucide-circle-question-mark-icon lucide-circle-question-mark cursor-pointer"
                            >
                                <circle cx="12" cy="12" r="10" />
                                <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" />
                                <path d="M12 17h.01" />
                            </svg>
                        </PopoverTrigger>
                        <PopoverContent class="w-auto max-w-md" align="start">
                            Recurring bills will be displayed on the calendar on their respective due dates.
                            <br /><br />
                            Here is the recurrence schedule for each bill type:
                            <ul class="mt-2 list-disc pl-5">
                                <li><strong>Weekly:</strong> Occurs every week for the next 52 weeks from the start date.</li>
                                <li><strong>Monthly:</strong> Occurs every month for the next 12 months from the start date.</li>
                                <li><strong>Yearly:</strong> Occurs once a year for the next 5 years from the start date.</li>
                            </ul>
                        </PopoverContent>
                    </Popover>
                </HeadingSmall>

                <EventCalendar :events="events" @new-event="handleNewEventClick" @select-event="handleEventSelect" />
            </div>
        </div>
        <Dialog :open="Boolean(openNewEventDialog)" @update:open="handleDialogClose">
            <DialogContent class="max-h-3/4 overflow-y-auto sm:max-w-2xl md:max-h-max">
                <template v-if="selectedEvent?.id && bill && !editSelectedEvent">
                    <Details :bill="bill" class="border-none shadow-none">
                        <template #footer>
                            <Link class="w-auto justify-self-end" :href="route('bills.show', bill.id)">
                                <Button variant="outline">
                                    <ScanEye class="mr-1 size-4" />
                                    Details
                                </Button>
                            </Link>
                            <Button @click="editSelectedEvent = true">
                                <Pen class="mr-1 size-4" />
                                Edit
                            </Button>
                        </template>
                    </Details>
                </template>

                <template v-else>
                    <DialogHeader class="text-left">
                        <DialogTitle>{{ selectedEvent?.id ? 'Edit Bill' : 'Create Bill' }}</DialogTitle>
                        <DialogDescription> Enter your bill details. </DialogDescription>
                    </DialogHeader>
                    <BillForm
                        :categories="props.categories"
                        :submit-url="editSelectedEvent && bill ? route('bills.update', bill.id) : route('bills.store')"
                        :submit-method="editSelectedEvent && bill ? 'put' : 'post'"
                        :options="{
                            onSuccess: () => handleDialogClose(),
                        }"
                        :tags="props.tags"
                        :bill="billItemFormData as BillData"
                    >
                        <Button variant="destructive" type="button" @click="editSelectedEvent = false" v-if="selectedEvent?.id && editSelectedEvent">
                            <Ban class="mr-1 size-4" />
                            Cancel
                        </Button>
                    </BillForm>
                </template>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
