<?php

namespace App\Filament\Armada\Resources\MasterTipeArmadaResource\Pages;

use App\Filament\Armada\Resources\MasterTipeArmadaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMasterTipeArmada extends EditRecord
{
    protected static string $resource = MasterTipeArmadaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
