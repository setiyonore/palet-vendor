<?php

namespace App\Filament\Armada\Resources\MasterStatusResource\Pages;

use App\Filament\Armada\Resources\MasterStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateMasterStatus extends CreateRecord
{
    protected static string $resource = MasterStatusResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
