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
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OneTimePasswordResource extends Resource
{
    protected static ?string $model = OneTimePassword::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('phone_number')
                    ->tel()
                    ->required()
                    ->maxLength(255)
                    ->disabled(),
                Forms\Components\TextInput::make('otp_code')
                    ->required()
                    ->maxLength(255)
                    ->disabled(),
                Forms\Components\Toggle::make('verified')
                    ->required()
                    ->disabled(),
                Forms\Components\DateTimePicker::make('expires_at')
                    ->required()
                    ->disabled(),
                Forms\Components\TextInput::make('attempts')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->disabled(),
            ]);
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
                    ->formatStateUsing(fn ($record) => $record->expires_at < now() ? 'Yes' : 'No'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
}
