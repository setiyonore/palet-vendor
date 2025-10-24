<?php

namespace App\Filament\PaletManagement\Resources\GudangPaletResource\Pages;

use App\Filament\PaletManagement\Resources\GudangPaletResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateGudangPalet extends CreateRecord
{
    protected static string $resource = GudangPaletResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
