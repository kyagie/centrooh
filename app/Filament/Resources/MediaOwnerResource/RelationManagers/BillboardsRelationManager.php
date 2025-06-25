<?php

namespace App\Filament\Resources\MediaOwnerResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;

class BillboardsRelationManager extends RelationManager
{
    protected static string $relationship = 'billboards';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('address')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('area')
                    ->maxLength(255),
                Forms\Components\TextInput::make('latitude')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('longitude')
                    ->numeric()
                    ->required(),
                Forms\Components\Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'pending' => 'Pending',
                        'review' => 'Under Review',
                    ])
                    ->required()
                    ->default('active'),
                Forms\Components\Toggle::make('is_active')
                    ->required()
                    ->default(true),
                Forms\Components\Select::make('district_id')
                    ->relationship('district', 'name')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('address')
                    ->searchable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('area')
                    ->searchable(),
                Tables\Columns\TextColumn::make('district.name')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                        'pending' => 'warning',
                        'review' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('last_updated')
                    ->label('Last Updated'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'pending' => 'Pending',
                        'review' => 'Under Review',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->infolist([
                        Section::make('Billboard Details')
                            ->schema([
                                TextEntry::make('name')
                                    ->label('Billboard Name'),
                                TextEntry::make('address')
                                    ->label('Address'),
                                TextEntry::make('area')
                                    ->label('Area'),
                                TextEntry::make('district.name')
                                    ->label('District'),
                                IconEntry::make('is_active')
                                    ->boolean()
                                    ->label('Active Status'),
                                TextEntry::make('status')
                                    ->badge()
                                    ->color(fn(string $state): string => match ($state) {
                                        'active' => 'success',
                                        'inactive' => 'danger',
                                        'pending' => 'warning',
                                        'review' => 'info',
                                        default => 'gray',
                                    }),
                            ])
                            ->columns(2),
                        Section::make('Location Information')
                            ->schema([
                                TextEntry::make('latitude'),
                                TextEntry::make('longitude'),
                            ])
                            ->columns(2),
                    ]),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
