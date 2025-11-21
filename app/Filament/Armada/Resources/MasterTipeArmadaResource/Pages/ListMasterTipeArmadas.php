<?php

namespace App\Filament\Armada\Resources\MasterTipeArmadaResource\Pages;

use App\Filament\Armada\Resources\MasterTipeArmadaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMasterTipeArmadas extends ListRecords
{
    protected static string $resource = MasterTipeArmadaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
