<?php

namespace App\Enums;

use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum CanScrapeStatusEnum: int implements HasLabel, HasColor
{
    case YES = 1;
    case NO = 2;

    public function getLabel(): ?string
    {
        return match ($this) {
            self::YES => 'Yes',
            self::NO => 'No',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::YES => Color::Green,
            self::NO => Color::Red,
        };
    }
}
