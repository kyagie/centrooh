<?php

namespace App\Filament\Resources\BillboardImageResource\Pages;

use App\Filament\Resources\BillboardImageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBillboardImage extends EditRecord
{
    protected static string $resource = BillboardImageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
