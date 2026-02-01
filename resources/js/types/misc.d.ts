import type { PageProps } from '@inertiajs/core';
import type { LucideIcon } from 'lucide-vue-next';
import type { Config } from 'ziggy-js';
import { Team, User } from './model';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon;
    isActive?: boolean;
}

export type NotificationData = {
    id: number;
    type: string;
    title: string;
    description: string;
    data: Record<string, any>;
    created_at: string;
    url: string;
    created_time: string;
    read_at: string | null;
};

export type TeamData = {
    current: Team;
    items: Team[];
};

export interface SharedData extends PageProps {
    name: string;
    version: string;
    quote: { message: string; author: string };
    auth: Auth;
    team: TeamData;
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
    notifications: {
        unread: number;
        last: NotificationData;
    };
    flash: {
        success: string | null;
        error: string | null;
        warning: string | null;
        info: string | null;
    };
}

export type BreadcrumbItemType = BreadcrumbItem;

export interface PaginationLinks {
    first: string | null;
    last: string | null;
    prev: string | null;
    next: string | null;
}

export interface PaginationMeta {
    current_page: number;
    from: number;
    last_page: number;
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
    path: string;
    per_page: number;
    to: number;
    total: number;
}

export interface PaginatedData<T> {
    data: T[];
    links: PaginationLinks;
    meta: PaginationMeta;
}

export type PaginationData<T> = {
    current_page: number;
    data: T;
    first_page_url: string;
    from: string;
    last_page: string;
    last_page_url: string;
    links: {
        url: string | null;
        label: string;
        active: boolean;
    }[];
    next_page_url: null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: string;
    total: string;
};
