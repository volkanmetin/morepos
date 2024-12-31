<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case CASH = 'cash';
    case POS1 = 'pos1';
    case POS2 = 'pos2';

    public function label(): string
    {
        return match($this) {
            self::CASH => 'NAKİT',
            self::POS1 => 'POS 1',
            self::POS2 => 'POS 2',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::CASH => 'ri-money-dollar-circle-line',
            self::POS1 => 'ri-bank-card-line',
            self::POS2 => 'ri-bank-card-2-line',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function options(): array
    {
        return collect(self::cases())->map(fn ($method) => [
            'value' => $method->value,
            'text' => $method->label(),
            'icon' => $method->icon(),
        ])->toArray();
    }
} 