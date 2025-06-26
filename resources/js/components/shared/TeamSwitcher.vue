<script setup lang="ts">
import { CheckCircle, ChevronsUpDown, Plus } from 'lucide-vue-next';

import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { computed } from 'vue';

import { SidebarMenu, SidebarMenuButton, SidebarMenuItem, useSidebar } from '@/components/ui/sidebar';
import { SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import AppLogoIcon from './AppLogoIcon.vue';

const { isMobile } = useSidebar();

const { props } = usePage<SharedData>();

const activeTeam = computed(() => props.team.current);

const teams = computed(() => props.team.items);
</script>

<template>
    <SidebarMenu>
        <SidebarMenuItem>
            <DropdownMenu>
                <DropdownMenuTrigger as-child>
                    <SidebarMenuButton size="lg" class="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground">
                        <div
                            class="bg-sidebar-primary dark:bg-transparent dark:border dark:border-white text-sidebar-primary-foreground flex aspect-square size-8 items-center justify-center rounded-lg"
                        >
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
                <DropdownMenuContent
                    class="w-[--reka-dropdown-menu-trigger-width] min-w-56 rounded-lg"
                    align="start"
                    :side="isMobile ? 'bottom' : 'right'"
                    :side-offset="4"
                >
                    <DropdownMenuLabel class="text-muted-foreground text-xs"> Teams </DropdownMenuLabel>
                    <template v-for="team in teams" :key="team.name">
                        <DropdownMenuItem class="gap-0 p-0">
                            <Link :href="route('team.switch', { team: team.id })" class="flex w-full items-center gap-2 p-2">
                                <div class="flex size-6 items-center justify-center rounded-sm border">
                                    <img v-if="team.icon" :src="team.icon_url" alt="Icon" class="size-4 shrink-0" />
                                    <AppLogoIcon v-else class="size-4 shrink-0" />
                                </div>
                                {{ team.name }}
                                <CheckCircle class="mr-1 ml-auto" v-if="activeTeam.id === team.id" />
                            </Link>
                        </DropdownMenuItem>
                    </template>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem class="gap-0 p-0">
                        <Link :href="route('team.create')" class="flex w-full items-center gap-2 p-2">
                            <div class="bg-background flex size-6 items-center justify-center rounded-md border">
                                <Plus class="size-4" />
                            </div>
                            <div class="text-muted-foreground font-medium">Add team</div>
                        </Link>
                    </DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>
        </SidebarMenuItem>
    </SidebarMenu>
</template>
