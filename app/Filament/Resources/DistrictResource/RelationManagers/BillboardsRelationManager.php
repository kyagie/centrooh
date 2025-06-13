<?php

namespace App\Filament\Resources\DistrictResource\RelationManagers;

use App\Models\Billboard;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Cheesegrits\FilamentGoogleMaps\Fields\Map;

class BillboardsRelationManager extends RelationManager
{
    protected static string $relationship = 'billboards';

    protected static ?string $recordTitleAttribute = 'name';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->description('Enter the basic details of the billboard')
                    ->collapsible()
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'pending' => 'Pending',
                                        'updated' => 'Updated',
                                        'rejected' => 'Rejected',
                                        'in_review' => 'In Review',
                                        'passed' => 'Passed',
                                    ])
                                    ->required(),
                            ]),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('media_owner_id')
                                    ->label('Media Owner')
                                    ->relationship('mediaOwner', 'name')
                                    ->searchable(),
                                Forms\Components\Toggle::make('is_active')
                                    ->label('Is Active')
                                    ->required(),
                            ]),
                        Forms\Components\Select::make('update_interval')
                            ->options([
                                'daily' => 'Daily',
                                'weekly' => 'Weekly',
                                'biweekly' => 'Biweekly',
                                'monthly' => 'Monthly',
                                'bimonthly' => 'Bimonthly',
                                'quarterly' => 'Quarterly',
                            ])
                            ->default('monthly'),
                    ]),
                
                Forms\Components\Section::make('Location Details')
                    ->description('Specify the location information for this billboard')
                    ->collapsible()
                    ->schema([
                        Forms\Components\Grid::make(1)
                            ->schema([
                                Forms\Components\Textarea::make('address')
                                    ->label('Address')
                                    ->autosize()
                                    ->maxLength(1024)
                                    ->required(),
                            ]),
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('area')
                                    ->label('Location')
                                    ->placeholder('Start typing to search for a location')
                                    ->maxLength(1024)
                                    ->required()
                                    ->columnSpan(1),
                                Forms\Components\TextInput::make('latitude')
                                    ->label('Latitude')
                                    ->lazy()
                                    ->columnSpan(1),
                                Forms\Components\TextInput::make('longitude')
                                    ->label('Longitude')
                                    ->lazy()
                                    ->columnSpan(1),
                            ]),
                        Map::make('map')
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                $set('latitude', $state['lat']);
                                $set('longitude', $state['lng']);
                            })
                            ->mapControls([
                                'mapTypeControl'    => false,
                                'scaleControl'      => true,
                                'streetViewControl' => false,
                                'rotateControl'     => false,
                                'fullscreenControl' => true,
                                'searchBoxControl'  => false,
                                'zoomControl'       => true,
                            ])
                            ->height(fn() => '400px')
                            ->defaultZoom(12)
                            ->defaultLocation(fn($record) => [
                                $record->latitude ?? 0.3401327,
                                $record->longitude ?? 32.5864384,
                            ])
                            ->draggable()
                            ->clickable(false)
                            ->autocomplete('area', placeField: 'name', types: [
                                'geocode',
                                'establishment',
                            ], countries: ['UG'])
                            ->autocompleteReverse()
                            ->geolocate()
                            ->geolocateOnLoad(true, false)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'updated' => 'success',
                        'rejected' => 'danger',
                        'in_review' => 'primary',
                        'passed' => 'success',
                    })
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('area')
                    ->label('Location')
                    ->searchable(),
                Tables\Columns\TextColumn::make('agents.user.name')
                    ->label('Assigned Agents')
                    ->listWithLineBreaks()
                    ->limitList(2)
                    ->expandableLimitedList(),
                Tables\Columns\TextColumn::make('mediaOwner.name')
                    ->label('Media Owner')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'updated' => 'Updated',
                        'rejected' => 'Rejected',
                        'in_review' => 'In Review',
                        'passed' => 'Passed',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->placeholder('All Billboards')
                    ->trueLabel('Active Billboards')
                    ->falseLabel('Inactive Billboards'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
