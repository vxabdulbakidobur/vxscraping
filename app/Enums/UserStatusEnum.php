<?php

namespace App\Enums;

use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum UserStatusEnum: int implements HasLabel, HasColor
{
    case PENDING = 0;
    case ACTIVE = 1;
    case INACTIVE = 2;

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::ACTIVE => 'Active',
            self::INACTIVE => 'Inactive',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::PENDING => Color::Gray,
            self::ACTIVE => Color::Green,
            self::INACTIVE => Color::Red,
        };
    }
}
