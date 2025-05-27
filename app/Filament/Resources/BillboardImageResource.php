<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BillboardImageResource\Pages;
use App\Filament\Resources\BillboardImageResource\RelationManagers;
use App\Models\BillboardImage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BillboardImageResource extends Resource
{
    protected static ?string $model = BillboardImage::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'Billboard Management';
    

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('billboard_id')
                    ->label('Billboard Name')
                    ->relationship('billboard', 'name')
                    ->searchable()
                    ->required(),
                Forms\Components\FileUpload::make('image_path')
                    ->label('Image')
                    ->image()
                    ->disk('do')
                    ->imageEditor()
                    ->visibility('public')
                    ->downloadable()
                    ->imageEditorAspectRatios([
                        null,
                        '16:9',
                        '4:3',
                        '1:1',
                    ]),
                // Forms\Components\TextInput::make('image_type'),
                Forms\Components\Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'pending' => 'Pending',
                        'reviewed' => 'Reviewed',
                        'rejected' => 'Rejected',
                        'in_review' => 'In Review',
                        'passed' => 'Passed',
                        'updated' => 'Updated',
                    ])
                    ->default('pending'),
                // Forms\Components\Select::make('agent_id')
                //     ->label('Agent Assigned')
                //     ->relationship('agent', 'username')
                //     ->searchable(),
                // Forms\Components\TextInput::make('meta'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('billboard.name'),
                Tables\Columns\ImageColumn::make('image_path')->simpleLightbox(),
                // Tables\Columns\ImageColumn::make('image_type'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable()
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
                // Tables\Columns\TextColumn::make('uploader_type'),
                // ->searchable(),
                // Tables\Columns\TextColumn::make('user_id')
                // ->numeric()
                // ->sortable(),
                // Tables\Columns\TextColumn::make('agent.username')
                // ->label('A')
                // ->searchable(),
                // ->numeric()
                // ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => Pages\ListBillboardImages::route('/'),
            'create' => Pages\CreateBillboardImage::route('/create'),
            'edit' => Pages\EditBillboardImage::route('/{record}/edit'),
        ];
    }
}
