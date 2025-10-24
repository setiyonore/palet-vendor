<?php

namespace App\Imports\PM;

use App\Models\PM\GudangPalet;
use App\Models\PM\PaletStok;
use App\Models\PM\PaletStokHistori;
use App\Models\PM\PeminjamanManual;
use App\Models\PM\PeminjamanPalet; // <-- 1. Tambahkan use statement ini
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class PeminjamanManualImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure, WithBatchInserts
{
    use SkipsFailures;

    public int $created = 0;
    public array $duplicateNopolDbRows = [];
    public array $plantNotFoundRows = [];

    /**
     * Header Excel yang diharapkan:
     * nopol | qty | tgl_pinjam | kode_expeditur | plant_pinjam | no_spj | tgl_spj | tgl_pod
     */
    public function model(array $row)
    {
        $nopol = $row['nopol'] ?? null;
        $plantIdentifier = $row['plant_pinjam'] ?? null;

        // --- PENYEMPURNAAN LOGIKA VALIDASI NOPOL ---
        if ($nopol) {
            // Cek ke tabel transaksi API
            $peminjamanApiExists = PeminjamanPalet::where('nopol', $nopol)->where('status', 0)->exists();

            // Cek ke tabel transaksi manual
            $peminjamanManualExists = PeminjamanManual::where('nopol', $nopol)->where('status', 0)->exists();

            // Jika ditemukan di salah satu tabel, maka tolak
            if ($peminjamanApiExists || $peminjamanManualExists) {
                $this->duplicateNopolDbRows[] = [
                    'row'    => $row,
                    'alasan' => "Nopol '{$nopol}' sudah memiliki peminjaman aktif di sistem.",
                ];
                return null;
            }
        }
        // --- SELESAI PENYEMPURNAAN ---s

        $gudang = GudangPalet::where('kode_gudang', $plantIdentifier)->first();
        if (!$gudang) {
            $gudang = GudangPalet::where('nama_gudang', $plantIdentifier)->first();
        }

        if (!$gudang) {
            $this->plantNotFoundRows[] = [
                'row'    => $row,
                'alasan' => "Plant '{$plantIdentifier}' tidak ditemukan (baik sebagai kode maupun nama).",
            ];
            return null;
        }

        $qtyPinjam = (int) $row['qty'];

        DB::transaction(function () use ($row, $gudang, $qtyPinjam) {
            $peminjaman = PeminjamanManual::create([
                'nopol' => $row['nopol'],
                'qty' => $qtyPinjam,
                'tgl_pinjam' => Date::excelToDateTimeObject($row['tgl_pinjam']),
                'kode_expeditur' => $row['kode_expeditur'],
                'gudang_id' => $gudang->id,
                'no_spj' => $row['no_spj'],
                'tgl_spj' => isset($row['tgl_spj']) ? Date::excelToDateTimeObject($row['tgl_spj']) : null,
                'tgl_pod' => isset($row['tgl_pod']) ? Date::excelToDateTimeObject($row['tgl_pod']) : null,
                'status' => 0,
                'user_id' => Auth::id(),
            ]);

            // Pengurangan stok dinonaktifkan
            // $stok = PaletStok::where('gudang_id', $gudang->id)->lockForUpdate()->first();
            // if ($stok) {
            //     $stok->decrement('qty_rfi', $qtyPinjam);
            // }

            PaletStokHistori::create([
                'gudang_id' => $gudang->id,
                'referensi_id' => $peminjaman->id,
                'tipe_transaksi' => 'PEMINJAMAN_MANUAL',
                'user_id' => Auth::id(),
                'tgl_transaksi' => now(),
                'perubahan_rfi' => -$qtyPinjam,
                'keterangan' => "Input manual untuk Nopol {$peminjaman->nopol}",
            ]);
        });

        $this->created++;
        return null;
    }

    public function rules(): array
    {
        return [
            '*.nopol' => ['required', 'distinct'],
            '*.qty' => ['required', 'numeric', 'min:1'],
            '*.tgl_pinjam' => ['required', 'numeric'],
            '*.plant_pinjam' => ['required'],
        ];
    }

    public function customValidationMessages()
    {
        return [
            '*.nopol.required' => 'Nopol wajib diisi.',
            '*.nopol.distinct' => 'Nopol duplikat di dalam file Excel.',
            '*.qty.required' => 'Qty wajib diisi.',
            '*.qty.numeric' => 'Qty harus berupa angka.',
            '*.tgl_pinjam.required' => 'Tgl Pinjam wajib diisi.',
            '*.plant_pinjam.required' => 'Plant Pinjam wajib diisi.',
        ];
    }

    public function batchSize(): int
    {
        return 100;
    }
}
