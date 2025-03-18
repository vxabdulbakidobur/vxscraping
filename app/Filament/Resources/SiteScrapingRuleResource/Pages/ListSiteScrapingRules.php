<?php

namespace App\Filament\Resources\SiteScrapingRuleResource\Pages;

use App\Filament\Resources\SiteScrapingRuleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSiteScrapingRules extends ListRecords
{
    protected static string $resource = SiteScrapingRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
