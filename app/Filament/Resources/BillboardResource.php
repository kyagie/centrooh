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

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
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
                
                Forms\Components\Section::make('Assignment Information')
                    ->description('Assign a district and agent to this billboard')
                    ->collapsible()
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('district_id')
                                    ->label('District')
                                    ->options(District::get()->pluck('name', 'id')->toArray())
                                    ->searchable()
                                    ->required(),
                                Forms\Components\Select::make('agent_id')
                                    ->label('Assigned Agent')
                                    ->relationship('agent', 'username')
                                    ->searchable()
                                    ->disabled(fn (callable $get) => !$get('district_id'))
                                    ->helperText(
                                        fn (callable $get) => !$get('district_id')
                                            ? 'Select a district first to assign an agent.'
                                            : 'Select an agent to assign to this billboard.'
                                    )
                                    ->required(fn (callable $get) => !empty($get('agent_id')) ? !empty($get('district_id')) : false)
                                    ->rule(function (callable $get) {
                                        return function ($attribute, $value, $fail) use ($get) {
                                            if ($value && !$get('district_id')) {
                                                $fail('A district must be selected before assigning an agent.');
                                            }
                                        };
                                    }),
                            ]),
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

    public static function table(Table $table): Table
    {
        return $table
            // ->headerActions([
            //     ImportAction::make()
            //         ->importer(BillboardImporter::class)
            // ])
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
                //Todo: Request Billboard Image Action
                //Todo: Assign Agent Action
                
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
