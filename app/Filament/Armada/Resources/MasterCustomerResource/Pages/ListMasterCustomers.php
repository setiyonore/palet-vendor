<?php

namespace App\Filament\Armada\Resources\MasterCustomerResource\Pages;

use App\Filament\Armada\Resources\MasterCustomerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMasterCustomers extends ListRecords
{
    protected static string $resource = MasterCustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
