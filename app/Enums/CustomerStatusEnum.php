<?php

namespace App\Enums;

use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum CustomerStatusEnum: string implements HasLabel, HasColor
{
    case PENDING = 'pending';
    case ENABLED = 'enabled';
    case SUSPENDED = 'suspended';
    case DISABLED = 'disabled';


    public function getLabel(): ?string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::ENABLED => 'Enabled',
            self::SUSPENDED => 'Suspended',
            self::DISABLED => 'Disabled',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::PENDING => Color::Gray,
            self::ENABLED => Color::Green,
            self::SUSPENDED => Color::Amber,
            self::DISABLED => Color::Red,
        };
    }
}
