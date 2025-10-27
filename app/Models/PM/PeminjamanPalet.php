<?php

namespace App\Models\PM;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PeminjamanPalet extends Model
{
    // protected $connection = 'dbwh';
    protected $table = 'public.peminjaman_palet';
    protected $guarded = [];

    public function gudangPalet(): BelongsTo
    {
        return $this->belongsTo(GudangPalet::class, 'gudang_id');
    }

    public function gudangKembali(): BelongsTo
    {
        return $this->belongsTo(GudangPalet::class, 'gudang_kembali_id');
    }

    public function pengembalian(): HasOne
    {
        return $this->hasOne(PengembalianPalet::class, 'peminjaman_palet_id');
    }
    public function masterVendor(): BelongsTo
    {
        return $this->belongsTo(MasterVendor::class, 'kode_vendor', 'kode_fios');
    }
}
