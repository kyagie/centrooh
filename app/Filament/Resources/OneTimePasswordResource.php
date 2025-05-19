<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OneTimePasswordResource\Pages;
use App\Filament\Resources\OneTimePasswordResource\RelationManagers;
use App\Models\OneTimePassword;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OneTimePasswordResource extends Resource
{
    protected static ?string $model = OneTimePassword::class;

    protected static ?string $navigationIcon = 'heroicon-o-lock-open';

    protected static ?string $navigationGroup = 'Access Control';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('phone_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('otp_code')
                    ->searchable(),
                Tables\Columns\IconColumn::make('verified')
                    ->boolean(),
                Tables\Columns\TextColumn::make('expires_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('expired')
                    ->label('Expired')
                    ->formatStateUsing(fn($record) => $record->expires_at < now() ? 'Yes' : 'No'),
                Tables\Columns\TextColumn::make('attempts')
                    ->label('Attempts')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('10s')
            ->filters([
                //
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOneTimePasswords::route('/'),
            'create' => Pages\CreateOneTimePassword::route('/create'),
            'edit' => Pages\EditOneTimePassword::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }
}
