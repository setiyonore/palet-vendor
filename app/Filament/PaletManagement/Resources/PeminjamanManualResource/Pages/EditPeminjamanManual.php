<?php

namespace App\Filament\PaletManagement\Resources\PeminjamanManualResource\Pages;

use App\Filament\PaletManagement\Resources\PeminjamanManualResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPeminjamanManual extends EditRecord
{
    protected static string $resource = PeminjamanManualResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
