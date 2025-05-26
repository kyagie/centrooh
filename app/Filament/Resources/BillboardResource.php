<?php

namespace App\Filament\Resources;

use App\Filament\Imports\BillboardImporter;
use App\Filament\Resources\BillboardResource\Pages;
use App\Filament\Resources\BillboardResource\RelationManagers;
use App\Filament\Resources\BillboardResource\RelationManagers\ImagesRelationManager;
use App\Models\Billboard;
use App\Models\District;
use Cheesegrits\FilamentGoogleMaps\Fields\Map;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Actions\ImportAction;

class BillboardResource extends Resource
{
    protected static ?string $model = Billboard::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Billboard Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'updated' => 'Updated',
                        'rejected' => 'Rejected',
                        'in_review' => 'In Review',
                        'passed' => 'Passed',
                    ])
                    ->required(),
                Forms\Components\Select::make('agent_id')
                    ->label('Assigned Agent')
                    ->relationship('agent', 'username')
                    ->searchable(),
                Forms\Components\Toggle::make('is_active')
                    ->required(),
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
                Forms\Components\Select::make('district_id')
                    ->label('District')
                    ->options(District::get()->pluck('name', 'id')->toArray())
                    ->searchable()
                    ->required(),
                Forms\Components\Textarea::make('address')
                    ->label('Address')
                    ->autosize()
                    ->maxLength(1024)
                    ->required(),
                Forms\Components\TextInput::make('area')
                    ->label('Location')
                    ->placeholder('Start typing to search for a location')
                    ->maxLength(1024)
                    ->required(),
                Map::make('map')
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                        $set('lat', $state['lat']);
                        $set('lng', $state['lng']);
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
                    ->height(fn() => '800px')
                    ->defaultZoom(12)
                    ->defaultLocation(fn($record) => [
                        $record->lat ?? 0.3401327,
                        $record->lng ?? 32.5864384,
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
                Forms\Components\TextInput::make('lat')
                    ->label('Latitude')
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                        $set('map', [
                            'lat' => floatVal($state),
                            'lng' => floatVal($get('longitude')),
                        ]);
                    })
                    ->lazy(),
                Forms\Components\TextInput::make('lng')
                    ->label('Longitude')
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                        $set('map', [
                            'lat' => floatval($get('latitude')),
                            'lng' => floatVal($state),
                        ]);
                    })
                    ->lazy(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->headerActions([
                ImportAction::make()
                    ->importer(BillboardImporter::class)
            ])
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
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
                Tables\Columns\TextColumn::make('district.name')
                    ->label('District')
                    ->sortable(),
                Tables\Columns\TextColumn::make('agent.user.name')
                    ->label('Agent Assigned')
                    ->sortable(),
                Tables\Columns\TextColumn::make('mediaOwner.name')
                    ->label('Media Owner')
                    ->sortable(),
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
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [
            //
            ImagesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBillboards::route('/'),
            'create' => Pages\CreateBillboard::route('/create'),
            'edit' => Pages\EditBillboard::route('/{record}/edit'),
        ];
    }
}
