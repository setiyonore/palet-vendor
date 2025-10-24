<?php

namespace App\Filament\PaletManagement\Resources\PeminjamanManualResource\Pages;

use App\Filament\PaletManagement\Resources\PeminjamanManualResource;
use App\Imports\PM\PeminjamanManualImport;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Facades\Excel;

class CreatePeminjamanManual extends CreateRecord
{
    protected static string $resource = PeminjamanManualResource::class;

    /**
     * Menimpa alur pembuatan record default.
     * Alih-alih menyimpan data form, kita akan memproses file Excel yang di-upload.
     */
    protected function handleRecordCreation(array $data): Model
    {
        $filePath = $data['file_path'];
        $importer = new PeminjamanManualImport();

        // Menjalankan proses impor menggunakan file yang di-upload
        Excel::import($importer, $filePath, 'public');

        // Mengambil hasil dari proses impor untuk membuat laporan
        $created = $importer->created;
        $failures = $importer->failures();
        $duplicates = $importer->duplicateNopolDbRows;
        $plantNotFound = $importer->plantNotFoundRows;

        // Membuat notifikasi ringkasan
        $body = "<strong>Impor Selesai.</strong><br>";
        $body .= "{$created} baris berhasil diimpor.<br>";

        $totalGagal = count($failures) + count($duplicates) + count($plantNotFound);

        if ($totalGagal > 0) {
            $body .= "<strong>{$totalGagal} baris gagal:</strong>";
            $body .= "<ul>";
            foreach ($duplicates as $failure) {
                $body .= "<li>Baris untuk Nopol '{$failure['row']['nopol']}' gagal: {$failure['alasan']}</li>";
            }
            foreach ($plantNotFound as $failure) {
                $body .= "<li>Baris untuk Nopol '{$failure['row']['nopol']}' gagal: {$failure['alasan']}</li>";
            }
            foreach ($failures as $failure) {
                $body .= "<li>Baris {$failure->row()}: {$failure->errors()[0]}</li>";
            }
            $body .= "</ul>";

            Notification::make()
                ->warning()
                ->title('Impor Selesai dengan Beberapa Kegagalan')
                ->body($body)
                ->persistent()
                ->send();
        } else {
            Notification::make()
                ->success()
                ->title('Impor Berhasil')
                ->body("{$created} baris data peminjaman manual berhasil diimpor.")
                ->send();
        }

        // Return dummy model untuk mencegah error dan me-redirect ke halaman index
        return new (static::getModel());
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
