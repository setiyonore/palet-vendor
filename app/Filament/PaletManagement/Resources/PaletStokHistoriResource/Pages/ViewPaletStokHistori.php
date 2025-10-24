<?php

namespace App\Filament\PaletManagement\Resources\PaletStokHistoriResource\Pages;

use App\Filament\PaletManagement\Resources\PaletStokHistoriResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPaletStokHistori extends ViewRecord
{
    protected static string $resource = PaletStokHistoriResource::class;

    protected function getHeaderActions(): array
    {
        // Histori tidak bisa diedit atau dihapus dari halaman view
        return [];
    }
}
