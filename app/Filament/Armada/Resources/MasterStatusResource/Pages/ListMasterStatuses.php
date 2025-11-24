<?php

namespace App\Filament\Armada\Resources\MasterStatusResource\Pages;

use App\Filament\Armada\Resources\MasterStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMasterStatuses extends ListRecords
{
    protected static string $resource = MasterStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
