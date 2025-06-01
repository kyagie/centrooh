<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BillboardImageResource\Pages;
use App\Filament\Resources\BillboardImageResource\RelationManagers;
use App\Models\BillboardImage;
use App\Services\AgentNotificationService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Auth;

class BillboardImageResource extends Resource
{
    protected static ?string $model = BillboardImage::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'Billboard Management';

    protected static ?int $navigationSort = 2;

    public static function getStatusSelectField(string $name = 'status'): Forms\Components\Select
    {
        return Forms\Components\Select::make($name)
            ->options([
                // 'active' => 'Active',
                'pending' => 'Pending',
                // 'reviewed' => 'Reviewed',
                'rejected' => 'Rejected',
                'in_review' => 'In Review',
                'passed' => 'Passed',
                // 'updated' => 'Updated',
            ]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('billboard_id')
                    ->label('Billboard Name')
                    ->relationship('billboard', 'name')
                    ->disabled(),
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
                // self::getStatusSelectField(),
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
                        // 'active' => 'success',
                        'pending' => 'warning',
                        // 'reviewed' => 'primary',
                        'rejected' => 'danger',
                        'in_review' => 'primary',
                        'passed' => 'success',
                        // 'updated' => 'success',
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
            ])->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Action::make('review')
                    ->visible(fn(BillboardImage $record): bool => $record->status === 'pending' || $record->status === 'in_review')
                    ->label('Review')
                    ->icon('heroicon-o-check-circle')
                    ->form([
                        self::getStatusSelectField(),
                        TextInput::make('review_notes')
                            ->label('Review Notes')
                            ->required()
                            ->columnSpanFull(),
                    ])
                    ->action(function (array $data, BillboardImage $record) {
                        $record->status = $data['status'];
                        $record->save();
                        $record->reviews()->create([
                            'user_id' => Auth::id(),
                            'review_note' => $data['review_notes'],
                        ]);

                        app(AgentNotificationService::class)->sendBillboardReviewNotification(
                            $record,
                            $data['status'],
                            $data['review_notes']
                        );
                    })

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
            RelationManagers\ReviewsRelationManager::class,
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
