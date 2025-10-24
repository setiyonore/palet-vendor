<?php

namespace App\Filament\PaletManagement\Resources\GudangPaletResource\Pages;

use App\Filament\PaletManagement\Resources\GudangPaletResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGudangPalet extends EditRecord
{
    protected static string $resource = GudangPaletResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
