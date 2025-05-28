<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Map extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-map';

    protected static ?string $navigationLabel = 'Map';

    protected static ?string $title = 'Map';

    protected static string $view = 'filament.pages.map';
}
