<template>
    <div class="flex flex-1 flex-col overflow-auto">
        <div class="p-4 space-y-4">
            <div v-if="agendaEvents.length === 0" class="text-center py-8">
                <p class="text-muted-foreground">No events scheduled for the next {{ AgendaDaysToShow }} days</p>
            </div>

            <div v-for="(dayEvents, date) in groupedEvents" :key="date" class="space-y-2">
                <div class="flex items-center gap-3 pb-2 border-b border-border/50">
                    <div class="text-lg font-semibold">
                        {{ format(new Date(date), 'EEEE, MMMM d') }}
                    </div>
                    <div class="text-sm text-muted-foreground">
                        {{ format(new Date(date), 'yyyy') }}
                    </div>
                    <div v-if="isToday(new Date(date))"
                        class="text-xs bg-primary text-primary-foreground px-2 py-1 rounded-full">
                        Today
                    </div>
                </div>

                <div class="space-y-2 ml-4">
                    <div v-for="event in dayEvents" :key="event.id"
                        class="flex items-start gap-3 p-3 rounded-lg border hover:bg-accent/50 transition-colors cursor-pointer"
                        @click="$emit('eventSelect', event)">
                        <div class="flex-shrink-0">
                            <div class="w-3 h-3 rounded-full" :class="getEventColorClasses(event.color).split(' ')[0]">
                            </div>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="font-medium text-sm truncate">{{ event.title }}</div>
                            <div v-if="event.description" class="text-xs text-muted-foreground truncate mt-1">
                                {{ event.description }}
                            </div>
                            <div class="text-xs text-muted-foreground mt-1">
                                <span v-if="event.allDay">All day</span>
                                <span v-else>
                                    {{ format(new Date(event.start), 'h:mm a') }} -
                                    {{ format(new Date(event.end), 'h:mm a') }}
                                </span>
                                <span v-if="event.location" class="ml-2">
                                    📍 {{ event.location }}
                                </span>
                            </div>
                        </div>

                        <div class="flex-shrink-0">
                            <div class="text-xs text-muted-foreground">
                                {{ getDurationText(event) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { addDays, eachDayOfInterval, format, isSameDay, isToday, differenceInMinutes, differenceInDays } from 'date-fns'

import { getEventColorClasses } from './utils'
import { AgendaDaysToShow } from './constants'
import type { CalendarEvent } from './types'

interface Props {
  currentDate: Date
  events: CalendarEvent[]
}

const props = defineProps<Props>()

const emit = defineEmits<{
  eventSelect: [event: CalendarEvent]
}>()

const agendaDays = computed(() => {
  const start = props.currentDate
  const end = addDays(start, AgendaDaysToShow - 1)
  return eachDayOfInterval({ start, end })
})

const agendaEvents = computed(() => {
  return props.events.filter((event) => {
    const eventStart = new Date(event.start)
    const eventEnd = new Date(event.end)
    
    return agendaDays.value.some((day) => {
      return (
        isSameDay(day, eventStart) ||
        isSameDay(day, eventEnd) ||
        (day > eventStart && day < eventEnd)
      )
    })
  })
})

const groupedEvents = computed(() => {
  const grouped: Record<string, CalendarEvent[]> = {}
  
  agendaDays.value.forEach((day) => {
    const dateKey = format(day, 'yyyy-MM-dd')
    const dayEvents = agendaEvents.value.filter((event) => {
      const eventStart = new Date(event.start)
      const eventEnd = new Date(event.end)
      
      return (
        isSameDay(day, eventStart) ||
        isSameDay(day, eventEnd) ||
        (day > eventStart && day < eventEnd)
      )
    })
    
    if (dayEvents.length > 0) {
      // Sort events by start time, all-day events first
      grouped[dateKey] = dayEvents.sort((a, b) => {
        if (a.allDay && !b.allDay) return -1
        if (!a.allDay && b.allDay) return 1
        
        const aStart = new Date(a.start)
        const bStart = new Date(b.start)
        return aStart.getTime() - bStart.getTime()
      })
    }
  })
  
  return grouped
})

const getDurationText = (event: CalendarEvent): string => {
  if (event.allDay) {
    const start = new Date(event.start)
    const end = new Date(event.end)
    const days = differenceInDays(end, start) + 1
    return days === 1 ? 'All day' : `${days} days`
  }
  
  const minutes = differenceInMinutes(new Date(event.end), new Date(event.start))
  if (minutes < 60) {
    return `${minutes}m`
  } else if (minutes < 1440) { // Less than 24 hours
    const hours = Math.floor(minutes / 60)
    const remainingMinutes = minutes % 60
    return remainingMinutes > 0 ? `${hours}h ${remainingMinutes}m` : `${hours}h`
  } else {
    const days = Math.floor(minutes / 1440)
    const remainingHours = Math.floor((minutes % 1440) / 60)
    return remainingHours > 0 ? `${days}d ${remainingHours}h` : `${days}d`
  }
}
</script>
