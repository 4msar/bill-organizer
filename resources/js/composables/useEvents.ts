import { CalendarEvent } from '@/components/ui/event-calendar';
import { Bill, SharedData } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const SevenDaysInMiliseconds = 7 * 24 * 60 * 60 * 1000;

const getColorByItem = (item: Bill) => {
    if (item.status === 'paid') return 'emerald';
    if (item.status === 'overdue') return 'rose';

    // if due date in 7 days
    if (new Date(item.due_date as string).getTime() - new Date().getTime() < SevenDaysInMiliseconds) {
        return 'orange';
    }

    return 'sky';
};

export const eventMapper = (item: Bill): CalendarEvent => ({
    id: item.id.toString(),
    title: item.title,
    description: item.description,
    start: new Date(item.due_date as string),
    end: new Date(item.due_date as string),
    // get random color
    color: getColorByItem(item),
    location: item.payment_url,
});

export function useEvents() {
    const { props } = usePage<
        SharedData & {
            bills: Bill[];
        }
    >();

    const bills = ref(props.bills);

    watch(
        () => usePage<SharedData>().props.bills,
        (newBills) => {
            bills.value = newBills as Bill[];
        },
        { deep: true },
    );

    const events = computed<CalendarEvent[]>(() => {
        const items = bills.value.reduce<CalendarEvent[]>((acc, bill) => {
            acc.push(eventMapper(bill));

            if (bill.is_recurring && bill.recurrence_period) {
                acc.push(...generateRecurringEvents(bill));
            }

            return acc;
        }, [] as CalendarEvent[]);

        return items;
    });

    const generateRecurringEvents = (bill: Bill): CalendarEvent[] => {
        const events: CalendarEvent[] = [];
        const baseDate = new Date(bill.due_date as string);
        const currentDate = new Date();
        const recurrencePeriod = bill.recurrence_period;

        if (recurrencePeriod === 'monthly') {
            // Generate monthly recurrences for the next 12 months
            for (let i = 1; i <= 12; i++) {
                const nextDate = new Date(baseDate);
                nextDate.setMonth(baseDate.getMonth() + i);

                // Only add future events
                if (nextDate > currentDate) {
                    const newBill = {
                        ...bill,
                        due_date: nextDate.toISOString(),
                    };
                    events.push(eventMapper(newBill));
                }
            }
        } else if (recurrencePeriod === 'yearly') {
            // Generate yearly recurrences for the next 5 years
            for (let i = 1; i <= 5; i++) {
                const nextDate = new Date(baseDate);
                nextDate.setFullYear(baseDate.getFullYear() + i);

                const newBill = {
                    ...bill,
                    due_date: nextDate.toISOString(),
                };
                events.push(eventMapper(newBill));
            }
        } else if (recurrencePeriod === 'weekly') {
            // Generate weekly recurrences for the next 52 weeks
            for (let i = 1; i <= 52; i++) {
                const nextDate = new Date(baseDate);
                nextDate.setDate(baseDate.getDate() + i * 7);

                // Only add events within the current year or next year
                if (nextDate.getFullYear() <= currentDate.getFullYear() + 1) {
                    const newBill = {
                        ...bill,
                        due_date: nextDate.toISOString(),
                    };
                    events.push(eventMapper(newBill));
                }
            }
        }

        return events;
    };

    return { events, bills };
}
