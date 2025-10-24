<?php

namespace App\Filament\PaletManagement\Resources\PaletStokResource\Pages;

use App\Filament\PaletManagement\Resources\PaletStokResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPaletStok extends EditRecord
{
    protected static string $resource = PaletStokResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
