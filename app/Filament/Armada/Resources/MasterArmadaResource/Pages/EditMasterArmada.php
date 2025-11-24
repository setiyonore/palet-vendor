<?php

namespace App\Filament\Armada\Resources\MasterArmadaResource\Pages;

use App\Filament\Armada\Resources\MasterArmadaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMasterArmada extends EditRecord
{
    protected static string $resource = MasterArmadaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
