<?php

namespace App\Models\PM;

use App\Models\User;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class PengembalianPalet extends Model
{
    // protected $connection = 'dbwh';
    protected $table = 'public.pengembalian_palet';
    protected $guarded = [];

    public function peminjamanPalet(): BelongsTo
    {
        return $this->belongsTo(PeminjamanPalet::class, 'peminjaman_palet_id');
    }

    public function peminjamanManual(): BelongsTo
    {
        return $this->belongsTo(PeminjamanManual::class, 'peminjaman_manual_id');
    }

    public function gudangPalet(): BelongsTo
    {
        return $this->belongsTo(GudangPalet::class, 'gudang_id');
    }

    /**
     * Accessor untuk mengambil nama pengguna dari koneksi database yang benar.
     * Ini menggantikan relasi BelongsTo 'user()' yang menyebabkan error.
     */
    protected function processor(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (is_null($this->user_id)) {
                    return 'Sistem';
                }

                // Menggunakan cache agar tidak query berulang kali untuk user yang sama.
                return Cache::remember("user_name_{$this->user_id}", 60, function () {
                    // Cari user di koneksi database default aplikasi.
                    $user = User::on(config('database.default'))->find($this->user_id);
                    return $user->name ?? 'User Tidak Ditemukan';
                });
            },
        );
    }
}
