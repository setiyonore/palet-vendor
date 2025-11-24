<?php

namespace App\Filament\Armada\Resources\DriverResource\Pages;

use App\Filament\Armada\Resources\DriverResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDriver extends CreateRecord
{

    protected static string $resource = DriverResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
