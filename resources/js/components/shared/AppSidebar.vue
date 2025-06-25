<script setup lang="ts">
import NavFooter from '@/components/shared/NavFooter.vue';
import NavMain from '@/components/shared/NavMain.vue';
import NavUser from '@/components/shared/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Currency, LayoutGrid, Receipt, Settings2, SunMoon, Tags } from 'lucide-vue-next';
import TeamSwitcher from './TeamSwitcher.vue';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Bills',
        href: '/bills',
        icon: Currency,
    },
    {
        title: 'Categories',
        href: '/categories',
        icon: Tags,
    },
    {
        title: 'Transactions',
        href: '/transactions',
        icon: Receipt,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Theme',
        href: '/settings/appearance',
        icon: SunMoon,
    },
    {
        title: 'Team settings',
        href: '/team/settings',
        icon: Settings2,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <TeamSwitcher :teams="[{
                name: 'Abc',
                plan: 'Test'
            }]" />
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
