<?php

namespace App\Filament\PaletManagement\Resources\PeminjamanManualResource\Pages;

use App\Filament\PaletManagement\Resources\PeminjamanManualResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPeminjamanManuals extends ListRecords
{
    protected static string $resource = PeminjamanManualResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
