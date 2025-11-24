<?php

namespace App\Filament\Armada\Resources\MasterPlantResource\Pages;

use App\Filament\Armada\Resources\MasterPlantResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateMasterPlant extends CreateRecord
{
    protected static string $resource = MasterPlantResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
