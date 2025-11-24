<?php

namespace App\Filament\Armada\Resources\MasterShipToResource\Pages;

use App\Filament\Armada\Resources\MasterShipToResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMasterShipTo extends EditRecord
{
    protected static string $resource = MasterShipToResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
