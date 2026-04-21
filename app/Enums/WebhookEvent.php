<?php

namespace App\Enums;

enum WebhookEvent: string
{
    case BillingCreated = 'billing.created';
    case BillingUpdated = 'billing.updated';
    case BillingDeleted = 'billing.deleted';
    case BillingPaid = 'billing.paid';
    case TransactionCreated = 'transaction.created';
    case TransactionDeleted = 'transaction.deleted';

    /**
     * Return a human-readable label for the event.
     */
    public function label(): string
    {
        return match ($this) {
            self::BillingCreated => 'Bill Created',
            self::BillingUpdated => 'Bill Updated',
            self::BillingDeleted => 'Bill Deleted',
            self::BillingPaid => 'Bill Paid',
            self::TransactionCreated => 'Transaction Created',
            self::TransactionDeleted => 'Transaction Deleted',
        };
    }

    /**
     * Return all event values.
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
