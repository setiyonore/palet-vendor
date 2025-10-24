<?php

namespace App\Imports\PM;

use App\Models\PM\MasterVendor;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class MasterVendorImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure, WithBatchInserts
{
    use SkipsFailures;

    public int $created = 0;
    public array $duplicateRows = [];

    // Properti untuk menyimpan kode yang sudah ada (diisi sekali saja)
    private array $existingFiosCodes;
    private array $existingSiCodes;

    public function __construct()
    {
        // Ambil semua kode yang sudah ada dari database dalam satu query tunggal.
        // Menggunakan array_flip untuk pencarian yang sangat cepat (O(1) lookup).
        $this->existingFiosCodes = array_flip(MasterVendor::pluck('kode_fios')->all());
        $this->existingSiCodes = array_flip(MasterVendor::pluck('kode_vendor_si')->all());
    }

    /**
     * Header Excel yang diharapkan:
     * kode_fios | kode_vendor_si | nama_vendor
     */
    public function model(array $row)
    {
        $kodeFios = $row['kode_fios'] ?? null;
        $kodeVendorSi = $row['kode_vendor_si'] ?? null;

        // --- PENGECEKAN DUPLIKAT YANG DIOPTIMALKAN ---
        // Pengecekan dilakukan terhadap array di memori, bukan query ke DB.
        if (
            ($kodeFios && isset($this->existingFiosCodes[$kodeFios])) ||
            ($kodeVendorSi && isset($this->existingSiCodes[$kodeVendorSi]))
        ) {
            $this->duplicateRows[] = [
                'row'    => $row,
                'alasan' => "Kode FIOS atau Kode Vendor SI sudah ada di database.",
            ];
            return null; // Lewati baris ini
        }
        // --- SELESAI ---

        $this->created++;

        // Tambahkan kode baru ke array agar tidak ada duplikat dari file Excel itu sendiri
        if ($kodeFios) $this->existingFiosCodes[$kodeFios] = true;
        if ($kodeVendorSi) $this->existingSiCodes[$kodeVendorSi] = true;

        return new MasterVendor([
            'kode_fios'      => $kodeFios,
            'kode_vendor_si' => $kodeVendorSi,
            'nama_vendor'    => $row['nama_vendor'],
            'keterangan'     => $row['keterangan'] ?? null,
        ]);
    }

    public function rules(): array
    {
        return [
            '*.kode_fios' => ['required', 'distinct'],
            '*.kode_vendor_si' => ['required', 'distinct'],
            '*.nama_vendor' => ['required'],
        ];
    }

    public function customValidationMessages()
    {
        return [
            '*.kode_fios.required' => 'Kolom kode_fios wajib diisi.',
            '*.kode_fios.distinct' => 'Terdapat duplikat kode_fios di dalam file Excel.',
            '*.kode_vendor_si.required' => 'Kolom kode_vendor_si wajib diisi.',
            '*.kode_vendor_si.distinct' => 'Terdapat duplikat kode_vendor_si di dalam file Excel.',
            '*.nama_vendor.required' => 'Kolom nama_vendor wajib diisi.',
        ];
    }

    public function batchSize(): int
    {
        return 200;
    }
}
