<?php

namespace App\Filament\Resources\OneTimePasswordResource\Pages;

use App\Filament\Resources\OneTimePasswordResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOneTimePasswords extends ListRecords
{
    protected static string $resource = OneTimePasswordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
