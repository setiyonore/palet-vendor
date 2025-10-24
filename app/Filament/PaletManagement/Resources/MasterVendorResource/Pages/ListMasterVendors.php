<?php

/**
 * File: app/Filament/PaletManagement/Resources/MasterVendorResource/Pages/ListMasterVendors.php
 */

namespace App\Filament\PaletManagement\Resources\MasterVendorResource\Pages;

use App\Filament\PaletManagement\Resources\MasterVendorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMasterVendors extends ListRecords
{
    protected static string $resource = MasterVendorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Create Vendor'),
        ];
    }
}
