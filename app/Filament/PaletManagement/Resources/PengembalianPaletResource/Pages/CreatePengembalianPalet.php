<?php

namespace App\Filament\PaletManagement\Resources\PengembalianPaletResource\Pages;

use App\Filament\PaletManagement\Resources\PengembalianPaletResource;
use App\Models\PM\PaletStok;
use App\Models\PM\PaletStokHistori;
use App\Models\PM\PeminjamanManual;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreatePengembalianPalet extends CreateRecord
{
    protected static string $resource = PengembalianPaletResource::class;

    /**
     * Logika pembuatan record disederhanakan untuk hanya menangani Peminjaman Manual.
     */
    protected function handleRecordCreation(array $data): Model
    {
        // Langsung cari dari PeminjamanManual menggunakan field 'peminjaman_manual_id'
        $peminjaman = PeminjamanManual::findOrFail($data['peminjaman_manual_id']);

        // Validasi total jumlah yang dikembalikan
        $totalKembali = (int)$data['qty_kembali_rfi'] + (int)$data['qty_kembali_tbr'] + (int)$data['qty_kembali_ber'];

        if ($totalKembali !== $peminjaman->qty) {
            throw ValidationException::withMessages([
                'data.qty_kembali_rfi' => "Total palet yang dikembalikan ({$totalKembali}) harus sama dengan jumlah yang dipinjam ({$peminjaman->qty}).",
            ]);
        }

        // Jalankan semua operasi database dalam satu transaksi yang aman
        return DB::transaction(function () use ($peminjaman, $data) {
            // 1. Buat record pengembalian baru
            $pengembalian = static::getModel()::create([
                // Hanya isi 'peminjaman_manual_id'
                'peminjaman_manual_id' => $peminjaman->id,
                'gudang_id'            => $data['gudang_id'],
                'tgl_kembali'          => $data['tgl_kembali'],
                'qty_kembali_rfi'      => $data['qty_kembali_rfi'],
                'qty_kembali_tbr'      => $data['qty_kembali_tbr'],
                'qty_kembali_ber'      => $data['qty_kembali_ber'],
                'ba_kembali_path'      => $data['ba_kembali_path'],
                'keterangan'           => $data['keterangan'],
                'user_id'              => Auth::id(),
            ]);

            // 2. Perbarui status peminjaman menjadi "Sudah Dikembalikan"
            $peminjaman->update(['status' => 1]);

            // 3. Perbarui stok palet di gudang tujuan
            $stok = PaletStok::where('gudang_id', $data['gudang_id'])->lockForUpdate()->firstOrFail();
            $stok->increment('qty_rfi', $data['qty_kembali_rfi']);
            $stok->increment('qty_tbr', $data['qty_kembali_tbr']);
            $stok->increment('qty_ber', $data['qty_kembali_ber']);

            // 4. Catat transaksi di tabel histori
            PaletStokHistori::create([
                'gudang_id'      => $data['gudang_id'],
                'referensi_id'   => $pengembalian->id,
                'tipe_transaksi' => 'PENGEMBALIAN',
                'user_id'        => Auth::id(),
                'tgl_transaksi'  => now(),
                'perubahan_rfi'  => $data['qty_kembali_rfi'],
                'perubahan_tbr'  => $data['qty_kembali_tbr'],
                'perubahan_ber'  => $data['qty_kembali_ber'],
                'keterangan'     => "Pengembalian manual dari Nopol {$peminjaman->nopol}",
            ]);

            return $pengembalian;
        });
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
