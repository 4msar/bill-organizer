<template>
  <div class="flex flex-1 flex-col">
    <!-- All-day events section -->
    <div v-if="allDayEvents.length > 0" class="border-b border-border/70 p-2">
      <div class="text-xs font-medium text-muted-foreground mb-2">All Day</div>
      <div class="space-y-1">
        <div v-for="event in allDayEvents" :key="event.id" class="cursor-pointer rounded px-2 py-1 text-xs"
          :class="getEventColorClasses(event.color)" @click="$emit('eventSelect', event)">
          {{ event.title }}
        </div>
      </div>
    </div>

    <!-- Week grid -->
    <div class="flex flex-1 overflow-auto">
      <!-- Time column -->
      <div class="flex-shrink-0 w-16 border-r border-border/70">
        <div class="h-10 border-b border-border/70"></div> <!-- Header spacer -->
        <div v-for="hour in hours" :key="hour.getTime()"
          class="relative border-b border-border/70 text-right pr-2 text-xs text-muted-foreground"
          :style="{ height: `${WeekCellsHeight}px` }">
          <div class="absolute -top-2 right-2">
            {{ format(hour, 'h a') }}
          </div>
        </div>
      </div>

      <!-- Days columns -->
      <div class="flex flex-1">
        <div v-for="(day, dayIndex) in days" :key="day.getTime()"
          class="flex-1 border-r border-border/70 last:border-r-0">
          <!-- Day header -->
          <div class="h-10 flex items-center justify-center border-b border-border/70 text-sm font-medium" :class="{
            'bg-accent text-accent-foreground': isToday(day)
          }">
            <div class="text-center">
              <div class="text-xs text-muted-foreground">{{ format(day, 'EEE').toUpperCase() }}</div>
              <div :class="{ 'text-primary': isToday(day) }">{{ format(day, 'd') }}</div>
            </div>
          </div>

          <!-- Day events -->
          <div class="relative" :style="{ height: `${totalHeight}px` }">
            <!-- Hour grid lines -->
            <div v-for="hour in hours" :key="hour.getTime()" class="absolute left-0 right-0 border-b border-border/70"
              :style="{
                top: `${(getHours(hour) - StartHour) * WeekCellsHeight}px`,
                height: `${WeekCellsHeight}px`
              }" @click="handleTimeSlotClick(day, hour)"></div>

            <!-- Events -->
            <div v-for="positionedEvent in processedDayEvents[dayIndex]" :key="positionedEvent.event.id"
              class="absolute cursor-pointer rounded px-1 py-0.5 text-xs"
              :class="getEventColorClasses(positionedEvent.event.color)" :style="{
                top: `${positionedEvent.top}px`,
                height: `${positionedEvent.height}px`,
                left: `${positionedEvent.left * 100}%`,
                width: `${positionedEvent.width * 100}%`,
                zIndex: positionedEvent.zIndex
              }" @click="$emit('eventSelect', positionedEvent.event)">
              <div class="font-medium truncate">{{ positionedEvent.event.title }}</div>
              <div class="text-xs opacity-75 truncate">
                {{ format(new Date(positionedEvent.event.start), 'h:mm a') }}
              </div>
            </div>

            <!-- Current time indicator -->
            <div v-if="shouldShowCurrentTime" class="absolute left-0 right-0 pointer-events-none z-20"
              :style="{ top: `${currentTimePosition}px` }">
              <div class="h-0.5 bg-red-500"></div>
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
  addHours,
  areIntervalsOverlapping,
  differenceInMinutes,
  eachDayOfInterval,
  eachHourOfInterval,
  endOfWeek,
  format,
  getHours,
  getMinutes,
  isSameDay,
  isToday,
  startOfDay,
  startOfWeek,
} from 'date-fns'

import { getEventColorClasses, isMultiDayEvent } from './utils'
import { WeekCellsHeight, StartHour, EndHour } from './constants'
import type { CalendarEvent } from './types'

interface Props {
  currentDate: Date
  events: CalendarEvent[]
}

interface PositionedEvent {
  event: CalendarEvent
  top: number
  height: number
  left: number
  width: number
  zIndex: number
}

const props = defineProps<Props>()

const emit = defineEmits<{
  eventSelect: [event: CalendarEvent]
  eventCreate: [startTime: Date]
}>()

const days = computed(() => {
  const weekStart = startOfWeek(props.currentDate, { weekStartsOn: 0 })
  const weekEnd = endOfWeek(props.currentDate, { weekStartsOn: 0 })
  return eachDayOfInterval({ start: weekStart, end: weekEnd })
})

const hours = computed(() => {
  const dayStart = startOfDay(props.currentDate)
  return eachHourOfInterval({
    start: addHours(dayStart, StartHour),
    end: addHours(dayStart, EndHour - 1),
  })
})

const totalHeight = computed(() => {
  return (EndHour - StartHour) * WeekCellsHeight
})

const allDayEvents = computed(() => {
  return props.events
    .filter((event) => {
      return event.allDay || isMultiDayEvent(event)
    })
    .filter((event) => {
      const eventStart = new Date(event.start)
      const eventEnd = new Date(event.end)
      return days.value.some(
        (day) =>
          isSameDay(day, eventStart) ||
          isSameDay(day, eventEnd) ||
          (day > eventStart && day < eventEnd)
      )
    })
})

const processedDayEvents = computed(() => {
  return days.value.map((day) => {
    // Get events for this day that are not all-day events
    const dayEvents = props.events.filter((event) => {
      if (event.allDay || isMultiDayEvent(event)) return false

      const eventStart = new Date(event.start)
      const eventEnd = new Date(event.end)

      return (
        isSameDay(day, eventStart) ||
        isSameDay(day, eventEnd) ||
        (eventStart < day && eventEnd > day)
      )
    })

    // Sort events by start time and duration
    const sortedEvents = [...dayEvents].sort((a, b) => {
      const aStart = new Date(a.start)
      const bStart = new Date(b.start)
      const aEnd = new Date(a.end)
      const bEnd = new Date(b.end)

      if (aStart < bStart) return -1
      if (aStart > bStart) return 1

      const aDuration = differenceInMinutes(aEnd, aStart)
      const bDuration = differenceInMinutes(bEnd, bStart)
      return bDuration - aDuration
    })

    // Calculate positions for each event
    const positionedEvents: PositionedEvent[] = []
    const dayStart = startOfDay(day)

    // Track columns for overlapping events
    const columns: { event: CalendarEvent; end: Date }[][] = []

    sortedEvents.forEach((event) => {
      const eventStart = new Date(event.start)
      const eventEnd = new Date(event.end)

      // Adjust start and end times if they're outside this day
      const adjustedStart = isSameDay(day, eventStart) ? eventStart : dayStart
      const adjustedEnd = isSameDay(day, eventEnd) ? eventEnd : addHours(dayStart, 24)

      // Calculate top position and height
      const startHour = getHours(adjustedStart) + getMinutes(adjustedStart) / 60
      const endHour = getHours(adjustedEnd) + getMinutes(adjustedEnd) / 60

      const top = (startHour - StartHour) * WeekCellsHeight
      const height = (endHour - startHour) * WeekCellsHeight

      // Find a column for this event
      let columnIndex = 0
      let placed = false

      while (!placed) {
        const col = columns[columnIndex] || []
        if (col.length === 0) {
          columns[columnIndex] = col
          placed = true
        } else {
          const overlaps = col.some((c) =>
            areIntervalsOverlapping(
              { start: adjustedStart, end: adjustedEnd },
              {
                start: new Date(c.event.start),
                end: new Date(c.event.end),
              }
            )
          )
          if (!overlaps) {
            placed = true
          } else {
            columnIndex++
          }
        }
      }

      const currentColumn = columns[columnIndex] || []
      columns[columnIndex] = currentColumn
      currentColumn.push({ event, end: adjustedEnd })

      // Calculate width and left position
      const width = columnIndex === 0 ? 1 : 0.9
      const left = columnIndex === 0 ? 0 : columnIndex * 0.1

      positionedEvents.push({
        event,
        top,
        height,
        left,
        width,
        zIndex: 10 + columnIndex,
      })
    })

    return positionedEvents
  })
})

const shouldShowCurrentTime = computed(() => {
  const now = new Date()
  return days.value.some(day => isSameDay(day, now))
})

const currentTimePosition = computed(() => {
  const now = new Date()
  const currentHour = getHours(now) + getMinutes(now) / 60
  return (currentHour - StartHour) * WeekCellsHeight
})

const handleTimeSlotClick = (day: Date, hour: Date) => {
  const startTime = new Date(day)
  startTime.setHours(getHours(hour), 0, 0, 0)
  emit('eventCreate', startTime)
}
</script>
