<?php

namespace App\Filament\Armada\Resources\MasterCityResource\Pages;

use App\Filament\Armada\Resources\MasterCityResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMasterCity extends EditRecord
{
    protected static string $resource = MasterCityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
