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
            ImportColumn::make('location')
                ->requiredMapping(),
            ImportColumn::make('latitude')
                ->requiredMapping(),
            ImportColumn::make('longitude')
                ->requiredMapping(),
            ImportColumn::make('district')
                ->relationship('district', 'name')
                ->requiredMapping()
                ->rules(['required', 'exists:districts,name']),
        ];
    }

    public function resolveRecord(): ?Billboard
    {
        // return Billboard::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new Billboard();
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
