<?php

namespace App\Filament\Resources\BillboardImageResource\Pages;

use App\Filament\Resources\BillboardImageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBillboardImages extends ListRecords
{
    protected static string $resource = BillboardImageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
