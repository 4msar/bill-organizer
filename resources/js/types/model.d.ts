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
}

export interface Category extends Model {
    user_id: number;
    name: string;
    description: string | null;
    icon: string | null;
}

export interface Bill extends Model {
    user_id: number;
    title: string;
    description: string | null;
    amount: number;
    due_date: string;
    status: 'paid' | 'unpaid';
    is_recurring: boolean;
    recurrence_period: 'weekly' | 'monthly' | 'yearly' | null;
    category_id: number | null;

    category?: Category;
    transactions?: Transaction[];
}

export interface Transaction extends Model {
    user_id: number;
    bill_id: number;
    amount: number;
    payment_date: string;
    payment_method: string | null;
    attachment: string | null;
    notes: string | null;
}
