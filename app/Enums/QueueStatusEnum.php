<?php

namespace App\Enums;

use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum QueueStatusEnum: string implements HasLabel, HasColor
{
    case PENDING = 'pending';
    case ON_QUEUE = 'on-queue';
    case COMPLETED = 'completed';
    case CANCELED = 'canceled';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::ON_QUEUE => 'On Queue',
            self::COMPLETED => 'Completed',
            self::CANCELED => 'Canceled',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::PENDING => Color::Gray,
            self::ON_QUEUE => Color::Blue,
            self::COMPLETED => Color::Green,
            self::CANCELED => Color::Orange,
        };
    }
}
