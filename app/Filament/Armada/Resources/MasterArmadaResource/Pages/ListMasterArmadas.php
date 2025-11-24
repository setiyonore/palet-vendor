<?php

namespace App\Filament\Armada\Resources\MasterArmadaResource\Pages;

use App\Filament\Armada\Resources\MasterArmadaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMasterArmadas extends ListRecords
{
    protected static string $resource = MasterArmadaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
