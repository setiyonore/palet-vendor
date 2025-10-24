<?php

namespace App\Filament\PaletManagement\Resources\PeminjamanPaletResource\Pages;

use App\Filament\PaletManagement\Resources\PeminjamanPaletResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPeminjamanPalet extends EditRecord
{
    protected static string $resource = PeminjamanPaletResource::class;

    /**
     * Properti untuk menampung data dari API saat tombol "Cek" ditekan.
     * Ini penting agar data baru bisa disimpan.
     */
    public bool $shipmentChecked = false;
    public array $shipmentPayload = [];

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * Method ini akan berjalan tepat sebelum data disimpan ke database.
     * gabungkan data baru dari api dan form
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Periksa apakah ada data baru yang berhasil diambil dari API
        if (isset($this->shipmentPayload['header']) && is_array($this->shipmentPayload['header'])) {
            $data = array_merge($this->shipmentPayload['header'], $data);
        }

        return $data;
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
