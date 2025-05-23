<?php

namespace App\Filament\Imports;

use App\Models\District;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class DistrictImporter extends Importer
{
    protected static ?string $model = District::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('status')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('region')
                ->relationship('region', 'name')
                ->requiredMapping()
                ->rules(['required', 'exists:regions,name'])
        ];
    }

    public function resolveRecord(): ?District
    {
        // return District::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new District();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your district import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
