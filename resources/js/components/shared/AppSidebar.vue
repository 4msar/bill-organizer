<script setup lang="ts">
import NavFooter from '@/components/shared/NavFooter.vue';
import NavMain from '@/components/shared/NavMain.vue';
import NavUser from '@/components/shared/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { Currency, LayoutGrid, Receipt, SunMoon, Tags } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

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
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
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
