<?php

namespace App\Filament\Armada\Resources\MasterJenisBarangResource\Pages;

use App\Filament\Armada\Resources\MasterJenisBarangResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateMasterJenisBarang extends CreateRecord
{
    protected static string $resource = MasterJenisBarangResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
