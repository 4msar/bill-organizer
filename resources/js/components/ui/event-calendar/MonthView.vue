<template>
    <div data-slot="month-view" class="contents">
        <div class="border-border/70 grid grid-cols-7 border-b">
            <div v-for="day in weekdays" :key="day"
                class="border-border/70 flex h-9 items-center justify-center border-r px-3 text-xs font-medium uppercase tracking-wide text-muted-foreground last:border-r-0">
                {{ day }}
            </div>
        </div>

        <div class="grid flex-1 grid-cols-7 grid-rows-6">
            <div v-for="(week, weekIndex) in weeks" :key="weekIndex" class="contents">
                <div v-for="day in week" :key="day.getTime()"
                    class="relative border-border/70 border-b border-r p-2 last:border-r-0 min-h-24" :class="{
            'bg-muted/30': !isSameMonth(day, currentDate),
            'bg-accent': isToday(day)
          }" @click="handleDayClick(day)">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium" :class="{
                'text-muted-foreground': !isSameMonth(day, currentDate),
                'text-accent-foreground': isToday(day)
              }">
                            {{ format(day, 'd') }}
                        </span>
                    </div>

                    <div class="mt-1 space-y-1">
                        <div v-for="event in getEventsForDay(events, day)" :key="event.id"
                            class="cursor-pointer rounded px-1 py-0.5 text-xs"
                            :class="getEventColorClasses(event.color)" @click.stop="$emit('eventSelect', event)">
                            {{ event.title }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import {
  addDays,
  eachDayOfInterval,
  endOfMonth,
  endOfWeek,
  format,
  isSameDay,
  isSameMonth,
  isToday,
  startOfMonth,
  startOfWeek,
} from 'date-fns'

import { getEventsForDay, getEventColorClasses } from './utils'
import { DefaultStartHour } from './constants'
import type { CalendarEvent } from './types'

interface Props {
  currentDate: Date
  events: CalendarEvent[]
}

const props = defineProps<Props>()

const emit = defineEmits<{
  eventSelect: [event: CalendarEvent]
  eventCreate: [startTime: Date]
}>()

const days = computed(() => {
  const monthStart = startOfMonth(props.currentDate)
  const monthEnd = endOfMonth(monthStart)
  const calendarStart = startOfWeek(monthStart, { weekStartsOn: 0 })
  const calendarEnd = endOfWeek(monthEnd, { weekStartsOn: 0 })

  return eachDayOfInterval({ start: calendarStart, end: calendarEnd })
})

const weekdays = computed(() => {
  return Array.from({ length: 7 }).map((_, i) => {
    const date = addDays(startOfWeek(new Date()), i)
    return format(date, 'EEE')
  })
})

const weeks = computed(() => {
  const result = []
  let week = []

  for (let i = 0; i < days.value.length; i++) {
    week.push(days.value[i])
    if (week.length === 7 || i === days.value.length - 1) {
      result.push(week)
      week = []
    }
  }

  return result
})

const handleDayClick = (day: Date) => {
  // Create a new event starting at the default hour
  const startTime = new Date(day)
  startTime.setHours(DefaultStartHour, 0, 0, 0)
  emit('eventCreate', startTime)
}
</script>
