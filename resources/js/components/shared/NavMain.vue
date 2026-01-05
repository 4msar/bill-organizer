<script setup lang="ts">
import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem, type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { PackagePlus } from 'lucide-vue-next';

defineProps<{
    items: NavItem[];
}>();

const page = usePage<SharedData>();

const currentLocation = page.props.ziggy.location;
</script>

<template>
    <SidebarGroup class="px-2 py-0" v-if="$page.props.team.current">
        <SidebarGroupLabel>Platform</SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem v-for="item in items" :key="item.title">
                <SidebarMenuButton as-child :is-active="item.href === currentLocation" :tooltip="item.title">
                    <Link :href="item.href">
                        <component :is="item.icon" />
                        <span>{{ item.title }}</span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
    <SidebarGroup class="px-2 py-0" v-else>
        <SidebarGroupLabel>Team</SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem>
                <SidebarMenuButton as-child tooltip="Create team">
                    <Link :href="route('team.create')">
                        <PackagePlus />
                        <span>Create team</span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
