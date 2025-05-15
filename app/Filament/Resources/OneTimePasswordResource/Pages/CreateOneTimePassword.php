<?php

namespace App\Filament\Resources\OneTimePasswordResource\Pages;

use App\Filament\Resources\OneTimePasswordResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateOneTimePassword extends CreateRecord
{
    protected static string $resource = OneTimePasswordResource::class;
}
