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
use App\Models\Agent;
use Illuminate\Database\Eloquent\Model;
use Filament\Infolists;
use Filament\Infolists\Infolist;

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
                Forms\Components\Select::make('agent_ids')
                    ->label('Agents')
                    ->multiple()
                    ->options(function () {
                        $agents = Agent::with('user')->get();
                        $options = $agents->pluck('user.name', 'id')->toArray();
                        return ['all' => 'All Agents'] + $options;
                    })
                    ->searchable()
                    ->searchPrompt('Search by name')
                    ->searchingMessage('Searching...')
                    ->noSearchResultsMessage('No results found')
                    ->required(),
                Forms\Components\Select::make('agent_notification_type_id')
                    ->label('Notification Type')
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
            ])->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                // Tables\Actions\EditAction::make(),
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
            'view' => Pages\ViewAgentNotification::route('/{record}'),
            'edit' => Pages\EditAgentNotification::route('/{record}/edit'),
        ];
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make()
                    ->schema([
                        Infolists\Components\TextEntry::make('agentNotificationType.name')
                            ->label('Notification Type'),
                        Infolists\Components\TextEntry::make('title')
                            ->label('Title'),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Sent At')
                            ->dateTime(),
                        Infolists\Components\TextEntry::make('read_at')
                            ->label('Read At')
                            ->dateTime()
                            ->placeholder('Not read yet'),
                    ])->columns(2),
                    
                Infolists\Components\Section::make('Notification Content')
                    ->schema([
                        Infolists\Components\TextEntry::make('body')
                            ->label('Content')
                            ->html(),
                    ]),
            ]);
    }
}
