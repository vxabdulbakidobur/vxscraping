<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum DefaultScrapingRuleTypeEnum: string implements HasLabel
{
    case NAME = 'name';
    case PRICE = 'price';
//    case SPECIAL_PRICE = 'special_price';
    case SKU = 'sku';
    case MPN = 'mpn';

//    case CUSTOM = 'custom';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PRICE => 'Price',
            self::NAME => 'Name',
//            self::SPECIAL_PRICE => 'Special Price',
            self::SKU => 'SKU',
            self::MPN => 'MPN',
//            self::CUSTOM => 'Custom',
        };
    }
}
