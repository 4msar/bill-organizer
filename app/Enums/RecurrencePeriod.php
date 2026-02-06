<?php

namespace App\Enums;

enum RecurrencePeriod: string
{
    case WEEKLY = 'weekly';
    case MONTHLY = 'monthly';
    case YEARLY = 'yearly';

    public static function values(): array
    {
        return array_map(fn ($case) => $case->value, self::cases());
    }

    public static function labels(): array
    {
        return [
            self::WEEKLY->value => 'Weekly',
            self::MONTHLY->value => 'Monthly',
            self::YEARLY->value => 'Yearly',
        ];
    }

    public function label(): string
    {
        return match ($this) {
            self::WEEKLY => 'Weekly',
            self::MONTHLY => 'Monthly',
            self::YEARLY => 'Yearly',
        };
    }
}
