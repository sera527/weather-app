<?php

namespace App\Enums;

enum FrequencyType: string
{
    case HOURLY = 'hourly';
    case DAILY = 'daily';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::HOURLY => 'Every Hour',
            self::DAILY => 'Once a Day',
        };
    }
}
