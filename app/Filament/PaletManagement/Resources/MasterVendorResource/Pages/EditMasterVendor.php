<?php

/**
 * File: app/Filament/PaletManagement/Resources/MasterVendorResource/Pages/EditMasterVendor.php
 */

namespace App\Filament\PaletManagement\Resources\MasterVendorResource\Pages;

use App\Filament\PaletManagement\Resources\MasterVendorResource;
use Filament\Resources\Pages\EditRecord;

class EditMasterVendor extends EditRecord
{
    protected static string $resource = MasterVendorResource::class;

    protected function getRedirectUrl(): ?string
    {
        return $this->getResource()::getUrl('index');
    }
}
