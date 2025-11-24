<?php

namespace App\Filament\Armada\Resources\MasterShipToResource\Pages;

use App\Filament\Armada\Resources\MasterShipToResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMasterShipTos extends ListRecords
{
    protected static string $resource = MasterShipToResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
