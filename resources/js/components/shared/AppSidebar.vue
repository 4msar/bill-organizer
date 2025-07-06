<script setup lang="ts">
import NavFooter from '@/components/shared/NavFooter.vue';
import NavMain from '@/components/shared/NavMain.vue';
import NavUser from '@/components/shared/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { SharedData, type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { Calendar1, Currency, LayoutGrid, Notebook, Receipt, Settings2, SunMoon, Tags } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
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
    {
        title: 'Calendar',
        href: route('calendar'),
        icon: Calendar1,
    },
    {
        title: 'Notes',
        href: route('notes.index'),
        icon: Notebook,
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

const page = usePage<SharedData>();

const filterFeatures = (item: NavItem) => {
    // Filter out items based on feature flags or conditions
    if (item.title === 'Notes' && !page.props.auth.user.metas.enable_notes) {
        return false;
    }
    if (item.title === 'Calendar' && !page.props.auth.user.metas.enable_calendar) {
        return false;
    }
    return true;
};

const filterFooterItems = (item: NavItem) => {
    // Filter out footer items based on feature flags or conditions
    if (item.title === 'Team settings' && page.props.team?.current?.user_id !== page.props.auth?.user?.id) {
        return false;
    }
    return true;
};
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <TeamSwitcher v-if="$page.props.team.current" />
            <SidebarMenu v-else>
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
            <NavMain :items="mainNavItems.filter(filterFeatures)" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter v-if="$page.props.team.current" :items="footerNavItems.filter(filterFooterItems)" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
