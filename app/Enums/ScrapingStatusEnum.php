<?php

namespace App\Enums;

use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ScrapingStatusEnum: int implements HasLabel, HasColor
{
    case ENABLED = 1;
    case DISABLED = 2;

    public function getLabel(): ?string
    {
        return match ($this) {
            self::ENABLED => 'Enabled',
            self::DISABLED => 'Disabled',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::ENABLED => Color::Green,
            self::DISABLED => Color::Red,
        };
    }
}
