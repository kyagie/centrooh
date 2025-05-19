<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AgentNotificationResource\Pages;
use App\Filament\Resources\AgentNotificationResource\RelationManagers;
use App\Models\AgentNotification;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AgentNotificationResource extends Resource
{
    protected static ?string $model = AgentNotification::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationGroup = 'Notifications';
    protected static ?string $navigationLabel = 'Agent Notifications';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('agent_id')
                    ->relationship('agent.user', 'name')
                    ->searchable([
                        'name'
                    ])
                    ->searchPrompt('Search by name')
                    ->searchingMessage('Searching...')
                    ->noSearchResultsMessage('No results found')
                    ->required(),
                Forms\Components\Select::make('agent_notification_type_id')
                    ->label('Notification Type')
                    ->relationship('agentNotificationType', 'name')
                    ->relationship('agentNotificationType', 'name')
                    ->searchable([
                        'name'
                    ])
                    ->searchPrompt('Search by name')
                    ->searchingMessage('Searching...')
                    ->noSearchResultsMessage('No results found')
                    ->required(),
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\RichEditor::make('body')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('agent.user.name')
                    ->label('Agent Name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('agentNotificationType.name')
                    ->label('Notification Type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('read_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
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
            'index' => Pages\ListAgentNotifications::route('/'),
            'create' => Pages\CreateAgentNotification::route('/create'),
            'edit' => Pages\EditAgentNotification::route('/{record}/edit'),
        ];
    }
}
