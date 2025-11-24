<?php

namespace App\Filament\Armada\Resources\MasterPlantResource\Pages;

use App\Filament\Armada\Resources\MasterPlantResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMasterPlants extends ListRecords
{
    protected static string $resource = MasterPlantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
