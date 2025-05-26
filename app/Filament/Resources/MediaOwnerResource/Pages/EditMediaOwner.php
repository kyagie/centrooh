<?php

namespace App\Filament\Resources\MediaOwnerResource\Pages;

use App\Filament\Resources\MediaOwnerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMediaOwner extends EditRecord
{
    protected static string $resource = MediaOwnerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
