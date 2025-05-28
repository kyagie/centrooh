<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Livewire\Attributes\Computed;
use App\Models\Billboard;

class Map extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-map';

    protected static ?string $navigationLabel = 'Map';

    protected static ?string $title = 'Map';

    protected static string $view = 'filament.pages.map';

    #[Computed(cache: true)]
    public function billboards(){
        return Billboard::all()->map(function ($billboard) {
            return [
                'lat' => $billboard->latitude,
                'long' => $billboard->longitude,
                'title' => $billboard->name,
            ];
        })->toArray();
    }

}
