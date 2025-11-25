<?php

namespace App\Filament\Armada\Resources\MasterJenisBarangResource\Pages;

use App\Filament\Armada\Resources\MasterJenisBarangResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMasterJenisBarang extends EditRecord
{
    protected static string $resource = MasterJenisBarangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
