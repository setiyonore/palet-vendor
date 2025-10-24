<?php

namespace App\Filament\PaletManagement\Resources\RekapPeminjamanResource\Pages;

use App\Filament\PaletManagement\Resources\RekapPeminjamanResource;
use App\Models\PM\PeminjamanManual;
use App\Models\PM\PeminjamanPalet;
use App\Models\PM\RekapPeminjamanRow;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ListRekapPeminjaman extends ListRecords
{
    protected static string $resource = RekapPeminjamanResource::class;

    protected function getTableQuery(): Builder
    {
        // Query untuk data dari transaksi API, digabungkan dengan master vendor
        $apiQuery = PeminjamanPalet::query()
            ->select(
                'peminjaman_palet.id',
                'peminjaman_palet.tgl_peminjaman',
                'peminjaman_palet.nopol',
                'peminjaman_palet.qty',
                DB::raw("'api' as source_type"),
                'master_vendor.nama_vendor'
            )
            ->leftJoin('master_vendor', 'peminjaman_palet.kode_vendor', '=', 'master_vendor.kode_fios')
            ->where('peminjaman_palet.status', 0);

        // Query untuk data dari peminjaman manual, digabungkan dengan master vendor
        $manualQuery = PeminjamanManual::query()
            ->select(
                'peminjaman_manual.id',
                'peminjaman_manual.tgl_pinjam as tgl_peminjaman',
                'peminjaman_manual.nopol',
                'peminjaman_manual.qty',
                DB::raw("'manual' as source_type"),
                'master_vendor.nama_vendor'
            )
            ->leftJoin('master_vendor', 'peminjaman_manual.kode_expeditur', '=', 'master_vendor.kode_vendor_si')
            ->where('peminjaman_manual.status', 0);

        // Menggabungkan kedua hasil query menggunakan UNION
        $apiQuery->unionAll($manualQuery);

        // Menggunakan hasil UNION sebagai subquery
        return RekapPeminjamanRow::query()
            ->fromSub($apiQuery, 'peminjaman_aktif')
            ->reorder()
            ->orderBy('tgl_peminjaman', 'asc');
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
