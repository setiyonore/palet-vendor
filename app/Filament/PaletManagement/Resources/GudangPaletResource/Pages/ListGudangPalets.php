<?php

namespace App\Filament\PaletManagement\Resources\GudangPaletResource\Pages;

use App\Filament\PaletManagement\Resources\GudangPaletResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGudangPalets extends ListRecords
{
    protected static string $resource = GudangPaletResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
