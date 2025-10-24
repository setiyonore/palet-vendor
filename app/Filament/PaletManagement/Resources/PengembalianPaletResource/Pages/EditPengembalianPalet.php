<?php

namespace App\Filament\PaletManagement\Resources\PengembalianPaletResource\Pages;

use App\Filament\PaletManagement\Resources\PengembalianPaletResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPengembalianPalet extends EditRecord
{
    protected static string $resource = PengembalianPaletResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * Memodifikasi data sebelum form diisi.
     * Ini adalah kunci untuk membuat dropdown berfungsi di halaman edit.
     */
    protected function mutateDataBeforeFill(array $data): array
    {
        $record = $this->getRecord();

        // Tentukan 'jenis_peminjaman' berdasarkan kolom mana yang terisi
        if ($record->peminjaman_palet_id) {
            $data['jenis_peminjaman'] = 'api';
            // Nama field form sudah 'peminjaman_palet_id', jadi tidak perlu diubah
        } elseif ($record->peminjaman_manual_id) {
            $data['jenis_peminjaman'] = 'manual';
            // Salin ID dari peminjaman manual ke field form 'peminjaman_palet_id'
            $data['peminjaman_palet_id'] = $record->peminjaman_manual_id;
        }

        return $data;
    }

    /**
     * Memodifikasi data sebelum disimpan.
     * Ini memastikan data disimpan kembali ke kolom yang benar.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $jenis = $data['jenis_peminjaman'];
        $peminjamanId = $data['peminjaman_palet_id'];

        if ($jenis === 'api') {
            $data['peminjaman_palet_id'] = $peminjamanId;
            $data['peminjaman_manual_id'] = null;
        } elseif ($jenis === 'manual') {
            $data['peminjaman_manual_id'] = $peminjamanId;
            $data['peminjaman_palet_id'] = null;
        }

        // Hapus field sementara agar tidak coba disimpan ke database
        unset($data['jenis_peminjaman']);

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
