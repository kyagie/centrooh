<?php

namespace App\Filament\Widgets;

use Cheesegrits\FilamentGoogleMaps\Actions\GoToAction;
use Cheesegrits\FilamentGoogleMaps\Actions\RadiusAction;
use Cheesegrits\FilamentGoogleMaps\Filters\RadiusFilter;
use Cheesegrits\FilamentGoogleMaps\Widgets\MapTableWidget;
use Cheesegrits\FilamentGoogleMaps\Columns\MapColumn;
use Cheesegrits\FilamentGoogleMaps\Filters\MapIsFilter;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

class BillboardsMap extends MapTableWidget
{
	protected static ?string $heading = 'Map Overview';

	protected static ?int $sort = 2;

	protected static ?string $pollingInterval = null;

	protected static ?bool $clustering = false;

	protected static ?string $mapId = 'billboards';

	protected static ?string $minHeight = '80vh';

	protected int | string | array $columnSpan = 'full';

	protected array $mapConfig = [
		'draggable' => false,
		'center'    => [
			'lat' => 1.3733,
			'lng' => 32.2903,
		],
		'zoom'       => 8,
		'fit'        => true,
		'gmaps'      => '',
		'clustering' => false,
		'mapConfig'  => [],
	];

	protected function getTableQuery(): Builder
	{
		return \App\Models\Billboard::query()->latest();
	}

	protected function getTableColumns(): array
	{
		return [
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
			Tables\Columns\TextColumn::make('mediaOwner.name')
				->label('Media Owner')
				->sortable(),
			MapColumn::make('location')
				->extraImgAttributes(
					fn($record): array => ['title' => $record->latitude . ',' . $record->longitude]
				)
				->height('150')
				->width('250')
				->type('hybrid')
				->zoom(15)
				->ttl(24 * 60 * 60)
		];
	}

	protected function getTableFilters(): array
	{
		return [
			RadiusFilter::make('radius')
				->latitude('lat')  
				->longitude('lng') 
				->selectUnit()
				->kilometers()
				->section('Radius Search'),
			MapIsFilter::make('map'),
		];
	}

	protected function getTableActions(): array
	{
		return [
			Tables\Actions\ViewAction::make()
				->url(fn($record): string => route('filament.admin.resources.billboards.edit', ['record' => $record]))
				->openUrlInNewTab(),
			GoToAction::make()
				->zoom(14),
			// RadiusAction::make(),
		];
	}

	protected function getData(): array
	{
		$locations = $this->getRecords();

		$data = [];

		foreach ($locations as $location) {
			$data[] = [
				'location' => [
					'lat' => $location->latitude ? round(floatval($location->latitude), static::$precision) : 0,
					'lng' => $location->longitude ? round(floatval($location->longitude), static::$precision) : 0,
				],
				'name'      => $location->name,
				'label'     => $location->name,
			];
		}

		return $data;
	}
}
