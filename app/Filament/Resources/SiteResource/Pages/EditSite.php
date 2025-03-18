<?php

namespace App\Filament\Resources\SiteResource\Pages;

use App\Enums\QueueStatusEnum;
use App\Filament\Resources\SiteResource;
use App\Models\Site;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Colors\Color;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Http;

class EditSite extends EditRecord
{
    protected static string $resource = SiteResource::class;

    public static bool $formActionsAreSticky = true;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),

            Actions\Action::make('start_scraping')
                ->icon('heroicon-o-play-circle')
                ->color(Color::Gray)
                ->hidden(fn(Site $record) => $record->queue_status === QueueStatusEnum::ON_QUEUE)
                ->requiresConfirmation()
                ->action(function (Site $record): void {
                    $this->startScraping($record);
                }),

            Actions\Action::make('stop_scraping')
                ->color(Color::Orange)
                ->icon('heroicon-o-pause-circle')
                ->visible(fn(Site $record) => $record->queue_status === QueueStatusEnum::ON_QUEUE)
                ->requiresConfirmation()
                ->action(function (Site $record): void {
                    $this->cancelScraping($record);
                }),

            Actions\Action::make('show_result')
                ->url(fn(Site $record) => route('products.scraping', [$record->token]), true)
        ];
    }

    protected function errorNotification(string $message): void
    {
        Notification::make('error')
            ->danger()
            ->title('Error!')
            ->body($message)
            ->send();
    }

    protected function successNotification(string $message): void
    {
        Notification::make('success')
            ->success()
            ->title('Success')
            ->body($message)
            ->send();
    }

    protected function startScraping(Site $site): void
    {
        $site->load([
            'siteCategories' => fn(Builder $query) => $query->where('status', 1),
            'siteScrapingRules' => fn(Builder $query) => $query->where('status', 1)
        ]);

        if (!$site->siteCategories->count()) {
            $this->errorNotification('There must be at least one active category!');
            return;
        }

        if (!$site->siteScrapingRules->count()) {
            $this->errorNotification('There must be at least one active rule!');
            return;
        }

        $request = Http::baseUrl(config('services.scrape.node_app_url'))
            ->post('scrape', ['site_profile' => $site->toArray()])
            ->json();

        if ($request['success']) {
            $site->queue_status = QueueStatusEnum::ON_QUEUE;
            $site->save();

            $this->successNotification($request['message']);
        }

        if (!$request['success']) {
            $this->errorNotification($request['message']);
        }
    }

    protected function cancelScraping(Site $site): void
    {
        $request = Http::baseUrl(config('services.scrape.node_app_url'))
            ->post('cancel_scraping', [
                'site_id' => $site->id
            ])
            ->json();

        if ($request['success']) {
            $site->queue_status = QueueStatusEnum::CANCELED;
            $site->save();

            $this->successNotification($request['message']);
        }

        if (!$request['success']) {
            $this->errorNotification($request['message']);
        }
    }
}
