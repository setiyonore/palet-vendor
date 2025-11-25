<?php

namespace App\Filament\Armada\Resources\MasterJenisBarangResource\Pages;

use App\Filament\Armada\Resources\MasterJenisBarangResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMasterJenisBarangs extends ListRecords
{
    protected static string $resource = MasterJenisBarangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
