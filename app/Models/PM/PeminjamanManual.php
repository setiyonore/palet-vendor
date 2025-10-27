<?php

namespace App\Models\PM;

use App\Models\User;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

class PeminjamanManual extends Model
{
    /**
     * Menentukan koneksi database ke data warehouse.
     */
    // protected $connection = 'dbwh';

    /**
     * Menentukan nama tabel di database.
     */
    protected $table = 'public.peminjaman_manual';

    /**
     * Mengizinkan semua kolom untuk diisi (mass assignment).
     */
    protected $guarded = [];

    /**
     * Mendefinisikan relasi ke model GudangPalet (Plant).
     */
    public function gudangPalet(): BelongsTo
    {
        return $this->belongsTo(GudangPalet::class, 'gudang_id');
    }

    public function masterVendor(): BelongsTo
    {
        return $this->belongsTo(MasterVendor::class, 'kode_expeditur', 'kode_vendor_si');
    }

    /**
     * Accessor untuk mengambil nama pengguna dari koneksi database yang benar.
     * Ini menggantikan relasi BelongsTo langsung.
     */
    protected function importer(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (is_null($this->user_id)) {
                    // Log::info("Accessor 'importer' dipanggil untuk ID: {$this->id}, user_id adalah NULL.");
                    return 'Sistem';
                }

                // Log sebelum query
                // Log::info("Accessor 'importer' dipanggil untuk ID: {$this->id}, mencoba mencari user_id: {$this->user_id}");

                // Cari user di koneksi database default aplikasi (tanpa cache untuk debugging)
                $user = User::on(config('database.default'))->find($this->user_id);

                if ($user) {
                    // Log jika user ditemukan
                    // Log::info("User ditemukan: {$user->name}");
                    return $user->name;
                } else {
                    // Log jika user tidak ditemukan
                    // Log::warning("User dengan ID {$this->user_id} tidak ditemukan di database default.");
                    return 'User Tidak Ditemukan';
                }
            },
        );
    }
}
