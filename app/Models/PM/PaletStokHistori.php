<?php

namespace App\Models\PM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Cache;

class PaletStokHistori extends Model
{
    use HasFactory;
    // protected $connection = 'dbwh';
    protected $table = 'public.palet_stok_histori';
    protected $guarded = [];
    const UPDATED_AT = null;

    public function gudangPalet()
    {
        return $this->belongsTo(GudangPalet::class, 'gudang_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    protected function performer(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (is_null($this->user_id)) {
                    return 'Sistem';
                }

                // Menggunakan cache untuk efisiensi, agar tidak query berulang kali untuk user yang sama.
                return Cache::remember("user_name_{$this->user_id}", 60, function () {
                    // Cari user di koneksi database default aplikasi.
                    $user = User::on(config('database.default'))->find($this->user_id);
                    return $user->name ?? 'User Tidak Ditemukan';
                });
            },
        );
    }
}
