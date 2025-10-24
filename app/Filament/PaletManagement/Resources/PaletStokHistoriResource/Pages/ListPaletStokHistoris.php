<?php

namespace App\Filament\PaletManagement\Resources\PaletStokHistoriResource\Pages;

use App\Filament\PaletManagement\Resources\PaletStokHistoriResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPaletStokHistoris extends ListRecords
{
    protected static string $resource = PaletStokHistoriResource::class;

    protected function getHeaderActions(): array
    {
        // Histori tidak bisa dibuat secara manual
        return [];
    }
}
