<?php

namespace App\Filament\Imports;

use App\Models\Billboard;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class BillboardImporter extends Importer
{
    protected static ?string $model = Billboard::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->requiredMapping(),
            ImportColumn::make('address')
                ->requiredMapping(),
            ImportColumn::make('area')
                ->requiredMapping(),
            ImportColumn::make('latitude')
                ->requiredMapping(),
            ImportColumn::make('longitude')
                ->requiredMapping(),
            ImportColumn::make('district')
                ->relationship('district', 'name'),
            ImportColumn::make('mediaOwner')
                ->relationship('mediaOwner', 'name')
                ->requiredMapping(),
        ];
    }

    public function resolveRecord(): ?Billboard
    {
        // Assuming 'name' can be used as a unique identifier for billboards
        // You may need to adjust this if there's another unique identifier
        return Billboard::firstOrNew([
            'name' => $this->data['name'],
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your billboard import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
