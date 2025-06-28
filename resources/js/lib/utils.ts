import { NotablePivot } from '@/types';
import { clsx, type ClassValue } from 'clsx';
import * as LucideIcons from 'lucide-vue-next';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function formatDate(date: string | number | Date) {
    const options: Intl.DateTimeFormatOptions = {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    };
    return new Date(date).toLocaleDateString('en-US', options);
}

export function formatCurrency(value: number, currency: string = 'USD') {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency,
        maximumFractionDigits: 2,
        currencyDisplay: 'narrowSymbol',
    }).format(value);
}

export function getIconComponent(iconName: string | null): LucideIcons.LucideIcon {
    if (!iconName) return LucideIcons.CircleDot;

    const pascalCase = iconName
        .split('-')
        .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
        .join('');

    return (LucideIcons[pascalCase as keyof typeof LucideIcons] as LucideIcons.LucideIcon) || LucideIcons.CircleDot;
}

export const getLink = (related: NotablePivot) => {
    return related.type === 'Bill' ? route('bills.show', related.notable_id) : route('transactions.show', related.notable_id);
};
