<?php

namespace App\Filament\Resources\SiteScrapingRuleResource\Pages;

use App\Filament\Resources\SiteScrapingRuleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSiteScrapingRule extends EditRecord
{
    protected static string $resource = SiteScrapingRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
