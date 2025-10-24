<?php

namespace App\Filament\PaletManagement\Resources\PengembalianPaletResource\Pages;

use App\Filament\PaletManagement\Resources\PengembalianPaletResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPengembalianPalets extends ListRecords
{
    protected static string $resource = PengembalianPaletResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
