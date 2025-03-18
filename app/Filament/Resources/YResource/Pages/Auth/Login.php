<?php

namespace App\Filament\Resources\YResource\Pages\Auth;

use App\Filament\Resources\YResource;
use Filament\Resources\Pages\Page;

class Login extends Page
{
    protected static string $resource = YResource::class;

    protected static string $view = 'filament.resources.y-resource.pages.auth.login';
}
