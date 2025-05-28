<?php

namespace App\Filament\Widgets;

use App\Models\BillboardImage;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class LatestBillboardImages extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';
    
    public function table(Table $table): Table
    {
        return $table
            ->heading('Latest Billboard Images')
            ->query(
                BillboardImage::query()->with(['billboard', 'agent.user'])->latest()->limit(5)
            )
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('Image')
                    ->simpleLightbox(),
                Tables\Columns\TextColumn::make('billboard.name')
                    ->label('Billboard')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'active' => 'success',
                        'pending' => 'warning',
                        'reviewed' => 'primary',
                        'rejected' => 'danger',
                        'in_review' => 'info',
                        'passed' => 'success',
                        'updated' => 'secondary',
                        default => 'secondary',
                    }),
                Tables\Columns\TextColumn::make('uploader_type')
                    ->label('Uploaded By')
                    ->formatStateUsing(fn ($state, BillboardImage $record) =>
                        ($state === 'agent' && $record->agent?->user)
                            ? 'Agent: ' . $record->agent->user->name
                            : (($state === 'user' && $record->user)
                                ? 'User: ' . $record->user->name
                                : ucfirst($state ?? 'Unknown'))
                    ),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Uploaded')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->url(fn (BillboardImage $record): string => route('filament.admin.resources.billboard-images.edit', ['record' => $record]))
                    ->icon('heroicon-m-eye'),
            ]);
    }
}
