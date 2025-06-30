export interface Model {
    id: number;
    created_at: string;
    updated_at: string;
}

type DataType = string | number | boolean | null;
export interface User extends Model {
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    metas: Record<string, DataType | DataType[]>;
    active_team_id: number | null;
}

export interface Category extends Model {
    user_id: number;
    team_id: number;
    name: string;
    description: string | null;
    icon: string | null;
}

export interface Bill extends Model {
    user_id: number;
    team_id: number;
    title: string;
    description: string;
    tags: string[]; // Array of tags
    amount: number;
    due_date?: string;
    status: 'paid' | 'unpaid';
    is_recurring: boolean;
    recurrence_period: 'weekly' | 'monthly' | 'yearly' | null;
    category_id: number | null;
    payment_url: string;

    category?: Category;
    transactions?: Transaction[];
    notes?: Note[];
}

export interface Transaction extends Model {
    user_id: number;
    team_id: number;
    bill_id: number;
    amount: number;
    payment_date: string;
    payment_method: string | null;
    attachment: string | null;
    notes: string | null;

    attachment_link?: string; // Computed property for attachment URL
    bill?: Bill;
}

export interface Team extends Model {
    user_id: number;
    name: string;
    description: string;
    icon: string;
    icon_url: string;
    status: string;
    currency: string;
    currency_symbol: string;
}

export type NotablePivot = {
    notable_id: number;
    notable_type: string;
    note_id: number;
    type: string;
    notable: Bill;
};

export interface Note extends Model {
    user_id: number;
    team_id: number;
    title: string;
    content: string;
    is_pinned: boolean;

    related?: NotablePivot[];
    team?: Team;
    user?: User;
}
