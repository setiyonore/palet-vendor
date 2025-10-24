<?php

namespace App\Filament\PaletManagement\Resources\RekapPeminjamanResource\Pages;

use App\Filament\PaletManagement\Resources\RekapPeminjamanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRekapPeminjaman extends EditRecord
{
    protected static string $resource = RekapPeminjamanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
