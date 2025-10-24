<?php

namespace App\Filament\PaletManagement\Resources\PaletStokResource\Pages;

use App\Filament\PaletManagement\Resources\PaletStokResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePaletStok extends CreateRecord
{
    protected static string $resource = PaletStokResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
