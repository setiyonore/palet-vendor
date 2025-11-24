<?php

namespace App\Filament\Armada\Resources\MasterArmadaResource\Pages;

use App\Filament\Armada\Resources\MasterArmadaResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateMasterArmada extends CreateRecord
{
    protected static string $resource = MasterArmadaResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
