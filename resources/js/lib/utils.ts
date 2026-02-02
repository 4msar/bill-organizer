import { NotablePivot } from '@/types';
import { clsx, type ClassValue } from 'clsx';
import * as LucideIcons from 'lucide-vue-next';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function formatDate(date: string | number | Date, includeTime: boolean = false): string {
    const options: Intl.DateTimeFormatOptions = {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    };

    if (includeTime) {
        options.hour = '2-digit';
        options.minute = '2-digit';
        options.hour12 = true;
    }

    return new Date(date).toLocaleDateString('en-US', options);
}

export function formatCurrency(value: number, currency: string = 'USD') {
    try {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: currency,
            maximumFractionDigits: 1,
            currencyDisplay: 'narrowSymbol',
        }).format(value);
        // eslint-disable-next-line @typescript-eslint/no-unused-vars
    } catch (error) {
        return '$' + value.toFixed(2);
    }
}

export const getCurrencyFullName = (currencyCode: string): string => {
    try {
        const displayNames = new Intl.DisplayNames(['en'], { type: 'currency' });
        return displayNames.of(currencyCode) ?? currencyCode;
        // eslint-disable-next-line @typescript-eslint/no-unused-vars
    } catch (error) {
        return currencyCode;
    }
};

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

export function getFileExtension(path: string): string {
    if (!path) return '';
    const parts = path.split('.');
    return parts[parts.length - 1].toLowerCase();
}

export function isImage(path: string): boolean {
    const ext = getFileExtension(path);
    return ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'].includes(ext);
}

export function isPdf(path: string): boolean {
    return getFileExtension(path) === 'pdf';
}

export function getVariantByStatus<T = string>(status: string): T {
    if (status === 'paid') return 'secondary' as T;
    if (status === 'overdue') return 'destructive' as T;

    return 'default' as T;
}

export function getDocumentType(path: string): string {
    const ext = getFileExtension(path);
    if (isImage(path)) return 'Image';
    if (isPdf(path)) return 'PDF';
    return ext.toUpperCase();
}

export const paymentMethods = {
    cash: 'Cash',
    credit_card: 'Credit Card',
    debit_card: 'Debit Card',
    bank_transfer: 'Bank Transfer',
    paypal: 'PayPal',
    crypto: 'Cryptocurrency',
    check: 'Check',
    other: 'Other',
} as const;

export function getPaymentMethodName(method: string | null): string {
    if (!method) return 'Other';
    return paymentMethods[method as keyof typeof paymentMethods] || method;
}

export function simpleMarkdown(text: string): string {
    return (
        text
            // sanitize tags
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            // line breaks (preserve for processing, will handle at end)
            .replace(/\r\n/g, '\n')

            // code blocks FIRST (to prevent other rules from affecting code content)
            .replace(/```([\s\S]*?)```/g, '<pre class="bg-muted p-2 rounded text-sm overflow-x-auto whitespace-pre border"><code>$1</code></pre>')

            // inline code (before bold/italic to prevent conflicts)
            .replace(/`([^`]+?)`/g, '<code class="bg-muted px-1 py-0.5 rounded text-sm border">$1</code>')

            // images (before links, since images use similar syntax)
            .replace(/!\[([^\]]*)\]\(([^)]+)\)/g, '<img src="$2" alt="$1" class="max-w-full h-auto" />')

            // links
            .replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" class="underline text-primary" target="_blank" rel="noopener noreferrer">$1</a>')

            // headings (process before line breaks)
            .replace(/^### (.+)$/gm, '<h3 class="text-lg font-medium">$1</h3>')
            .replace(/^## (.+)$/gm, '<h2 class="text-xl font-semibold">$1</h2>')
            .replace(/^# (.+)$/gm, '<h1 class="text-2xl font-bold">$1</h1>')

            // blockquotes
            .replace(/^&gt; (.+)$/gm, '<blockquote class="border-l-4 border-gray-300 pl-4 italic">$1</blockquote>')

            // bold + italic combined (***text*** or ___text___)
            .replace(/\*\*\*([^*]+)\*\*\*/g, '<strong><em>$1</em></strong>')
            .replace(/___([^_]+)___/g, '<strong><em>$1</em></strong>')

            // bold (**text** or __text__)
            .replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>')
            .replace(/__([^_]+)__/g, '<strong>$1</strong>')

            // italic (*text* or _text_)
            .replace(/\*([^*]+)\*/g, '<em>$1</em>')
            .replace(/_([^_]+)_/g, '<em>$1</em>')

            // unordered lists
            .replace(/\n^- (.+)$/gm, '• $1<br>')
            // ordered lists
            .replace(/\n^\d+\. (.+)$/gm, (match) => {
                const index = match.match(/^\n^(\d+)\. /m);
                return index ? `${index[1]}. ${match.slice(index[1].length + 3)}<br>` : match;
            })

            // tabs
            .replace(/\t/g, '&emsp;')

            // line breaks (last, to convert remaining newlines)
            .replace(/\n\n/g, '<br>')
    );
}
