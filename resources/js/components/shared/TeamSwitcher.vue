<script setup lang="ts">
import { CheckCircle, ChevronsUpDown, Plus } from 'lucide-vue-next';

import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuShortcut,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { type Component, computed, ref } from 'vue';

import {
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
  useSidebar,
} from '@/components/ui/sidebar';
import AppLogoIcon from './AppLogoIcon.vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { SharedData, Team } from '@/types';

const { isMobile } = useSidebar()

const { props } = usePage<SharedData>()

const activeTeam = computed(() => props.team.current);

const teams = computed(() => props.team.items)

</script>

<template>
  <SidebarMenu>
    <SidebarMenuItem>
      <DropdownMenu>
        <DropdownMenuTrigger as-child>
          <SidebarMenuButton size="lg"
            class="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground">
            <div
              class="flex aspect-square size-8 items-center justify-center rounded-lg bg-sidebar-primary text-sidebar-primary-foreground">
              <AppLogoIcon class="size-4" />
            </div>
            <div class="grid flex-1 text-left text-sm leading-tight">
              <span class="truncate font-semibold">
                {{ activeTeam.name }}
              </span>
              <span class="truncate text-xs">{{ $page.props.name }}</span>
            </div>
            <ChevronsUpDown class="ml-auto" />
          </SidebarMenuButton>
        </DropdownMenuTrigger>
        <DropdownMenuContent class="w-[--reka-dropdown-menu-trigger-width] min-w-56 rounded-lg" align="start"
          :side="isMobile ? 'bottom' : 'right'" :side-offset="4">
          <DropdownMenuLabel class="text-xs text-muted-foreground">
            Teams
          </DropdownMenuLabel>
          <template v-for="(team, index) in teams" :key="team.name">
            <DropdownMenuItem class="p-0 gap-0">
              <Link :href="route('team.switch', { team: team.id })" class="w-full flex items-center gap-2 p-2">
              <div class="flex size-6 items-center justify-center rounded-sm border">
                <img v-if="team.icon" :src="team.icon_url" alt="Icon" class="size-4 shrink-0" />
                <AppLogoIcon v-else class="size-4 shrink-0" />
              </div>
              {{ team.name }}
              <CheckCircle class="ml-auto mr-1" v-if="activeTeam.id === team.id" />
              </Link>
            </DropdownMenuItem>
          </template>
          <DropdownMenuSeparator />
          <DropdownMenuItem class="p-0 gap-0">
            <Link :href="route('team.create')" class="w-full flex items-center gap-2 p-2">
            <div class="flex size-6 items-center justify-center rounded-md border bg-background">
              <Plus class="size-4" />
            </div>
            <div class="font-medium text-muted-foreground">
              Add team
            </div>
            </Link>
          </DropdownMenuItem>
        </DropdownMenuContent>
      </DropdownMenu>
    </SidebarMenuItem>
  </SidebarMenu>
</template>
