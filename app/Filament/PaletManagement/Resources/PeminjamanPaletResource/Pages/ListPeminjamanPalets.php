<?php

namespace App\Filament\PaletManagement\Resources\PeminjamanPaletResource\Pages;

use App\Filament\PaletManagement\Resources\PeminjamanPaletResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPeminjamanPalets extends ListRecords
{
    protected static string $resource = PeminjamanPaletResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
