<?php

namespace App\Filament\Armada\Resources\MasterShipToResource\Pages;

use App\Filament\Armada\Resources\MasterShipToResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateMasterShipTo extends CreateRecord
{
    protected static string $resource = MasterShipToResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
