<script setup lang="ts">
import { addDays, addMonths, addWeeks, endOfWeek, format, isSameMonth, startOfWeek, subMonths, subWeeks } from 'date-fns'
import { CalendarCheck, ChevronDown, ChevronLeft, ChevronRight, Plus } from 'lucide-vue-next'
import { computed, onMounted, onUnmounted, ref } from 'vue'

import { Button } from '@/components/ui/button'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuShortcut,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import { cn } from '@/lib/utils'

import { AgendaDaysToShow, EventGap, EventHeight, WeekCellsHeight } from './constants'
import type { CalendarEvent, CalendarView } from './types'

import AgendaView from './AgendaView.vue'
import DayView from './DayView.vue'
import MonthView from './MonthView.vue'
import WeekView from './WeekView.vue'

export interface EventCalendarProps {
  events?: CalendarEvent[]
  className?: string
  initialView?: CalendarView
}

const props = withDefaults(defineProps<EventCalendarProps>(), {
  events: () => [],
  initialView: 'month'
})

defineEmits<{
  selectEvent: [event: CalendarEvent]
  newEvent: [startTime: Date]
}>()

const currentDate = ref(new Date())
const view = ref<CalendarView>(props.initialView)

const handleKeyDown = (e: KeyboardEvent) => {
  // Skip if user is typing in an input, textarea or contentEditable element
  // or if the event dialog is open
  if (
    e.target instanceof HTMLInputElement ||
    e.target instanceof HTMLTextAreaElement ||
    (e.target instanceof HTMLElement && e.target.isContentEditable)
  ) {
    return
  }

  switch (e.key.toLowerCase()) {
    case 'm':
      view.value = 'month'
      break
    case 'w':
      view.value = 'week'
      break
    case 'd':
      view.value = 'day'
      break
    case 'a':
      view.value = 'agenda'
      break
  }
}

onMounted(() => {
  window.addEventListener('keydown', handleKeyDown)
})

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeyDown)
})

const handlePrevious = () => {
  if (view.value === 'month') {
    currentDate.value = subMonths(currentDate.value, 1)
  } else if (view.value === 'week') {
    currentDate.value = subWeeks(currentDate.value, 1)
  } else if (view.value === 'day') {
    currentDate.value = addDays(currentDate.value, -1)
  } else if (view.value === 'agenda') {
    // For agenda view, go back 30 days (a full month)
    currentDate.value = addDays(currentDate.value, -AgendaDaysToShow)
  }
}

const handleNext = () => {
  if (view.value === 'month') {
    currentDate.value = addMonths(currentDate.value, 1)
  } else if (view.value === 'week') {
    currentDate.value = addWeeks(currentDate.value, 1)
  } else if (view.value === 'day') {
    currentDate.value = addDays(currentDate.value, 1)
  } else if (view.value === 'agenda') {
    // For agenda view, go forward 30 days (a full month)
    currentDate.value = addDays(currentDate.value, AgendaDaysToShow)
  }
}

const handleToday = () => {
  currentDate.value = new Date()
}

const setView = (newView: CalendarView) => {
  view.value = newView
}

const viewTitle = computed(() => {
  if (view.value === 'month') {
    return format(currentDate.value, 'MMMM yyyy')
  } else if (view.value === 'week') {
    const start = startOfWeek(currentDate.value, { weekStartsOn: 0 })
    const end = endOfWeek(currentDate.value, { weekStartsOn: 0 })
    if (isSameMonth(start, end)) {
      return format(start, 'MMMM yyyy')
    } else {
      return `${format(start, 'MMM')} - ${format(end, 'MMM yyyy')}`
    }
  } else if (view.value === 'day') {
    return format(currentDate.value, 'EEE MMMM d, yyyy')
  } else if (view.value === 'agenda') {
    // Show the month range for agenda view
    const start = currentDate.value
    const end = addDays(currentDate.value, AgendaDaysToShow - 1)

    if (isSameMonth(start, end)) {
      return format(start, 'MMMM yyyy')
    } else {
      return `${format(start, 'MMM')} - ${format(end, 'MMM yyyy')}`
    }
  } else {
    return format(currentDate.value, 'MMMM yyyy')
  }
})

</script>


<template>
  <div :class="cn('flex flex-col rounded-lg border has-data-[slot=month-view]:flex-1', className)" :style="{
    '--event-height': `${EventHeight}px`,
    '--event-gap': `${EventGap}px`,
    '--week-cells-height': `${WeekCellsHeight}px`,
  }">
    <div :class="cn('flex items-center justify-between p-2 sm:p-4')">
      <div class="flex items-center gap-1 sm:gap-4">
        <Button variant="outline" class="max-[479px]:aspect-square max-[479px]:p-0!" @click="handleToday">
          <CalendarCheck class="min-[480px]:hidden" :size="16" aria-hidden="true" />
          <span class="max-[479px]:sr-only">Today</span>
        </Button>
        <div class="flex items-center sm:gap-2">
          <Button variant="ghost" size="icon" @click="handlePrevious" aria-label="Previous">
            <ChevronLeft :size="16" aria-hidden="true" />
          </Button>
          <Button variant="ghost" size="icon" @click="handleNext" aria-label="Next">
            <ChevronRight :size="16" aria-hidden="true" />
          </Button>
        </div>
        <h2 class="text-sm font-semibold sm:text-lg md:text-xl">
          {{ viewTitle }}
        </h2>
      </div>
      <div class="flex items-center gap-2">
        <DropdownMenu>
          <DropdownMenuTrigger as-child>
            <Button variant="outline" class="gap-1.5 max-[479px]:h-8">
              <span>
                <span class="min-[480px]:hidden" aria-hidden="true">
                  {{ view.charAt(0).toUpperCase() }}
                </span>
                <span class="max-[479px]:sr-only">
                  {{ view.charAt(0).toUpperCase() + view.slice(1) }}
                </span>
              </span>
              <ChevronDown class="-me-1 opacity-60" :size="16" aria-hidden="true" />
            </Button>
          </DropdownMenuTrigger>
          <DropdownMenuContent align="end" class="min-w-32">
            <DropdownMenuItem @click="setView('month')">
              Month
              <DropdownMenuShortcut>M</DropdownMenuShortcut>
            </DropdownMenuItem>
            <DropdownMenuItem @click="setView('week')">
              Week
              <DropdownMenuShortcut>W</DropdownMenuShortcut>
            </DropdownMenuItem>
            <DropdownMenuItem @click="setView('day')">
              Day
              <DropdownMenuShortcut>D</DropdownMenuShortcut>
            </DropdownMenuItem>
            <DropdownMenuItem @click="setView('agenda')">
              Agenda
              <DropdownMenuShortcut>A</DropdownMenuShortcut>
            </DropdownMenuItem>
          </DropdownMenuContent>
        </DropdownMenu>
        <Button class="max-[479px]:aspect-square max-[479px]:p-0!" size="sm" @click="$emit('newEvent', new Date())">
          <Plus class="opacity-60 sm:-ms-1" :size="16" aria-hidden="true" />
          <span class="max-sm:sr-only">New Bill</span>
        </Button>
      </div>
    </div>

    <div class="flex flex-1 flex-col">
      <MonthView v-if="view === 'month'" :current-date="currentDate" :events="props.events"
        @event-select="item => $emit('selectEvent', item)" @event-create="date => $emit('newEvent', date)" />
      <WeekView v-else-if="view === 'week'" :current-date="currentDate" :events="props.events"
        @event-select="item => $emit('selectEvent', item)" @event-create="date => $emit('newEvent', date)" />
      <DayView v-else-if="view === 'day'" :current-date="currentDate" :events="props.events"
        @event-select="item => $emit('selectEvent', item)" @event-create="date => $emit('newEvent', date)" />
      <AgendaView v-else-if="view === 'agenda'" :current-date="currentDate" :events="props.events"
        @event-select="item => $emit('selectEvent', item)" />
    </div>

    <slot />
  </div>
</template>
