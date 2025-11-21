<?php

namespace App\Filament\Armada\Resources\MasterTipeArmadaResource\Pages;

use App\Filament\Armada\Resources\MasterTipeArmadaResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateMasterTipeArmada extends CreateRecord
{
    protected static string $resource = MasterTipeArmadaResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
