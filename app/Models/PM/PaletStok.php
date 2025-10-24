<?php

namespace App\Models\PM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class PaletStok extends Model
{
    use HasFactory;
    protected $connection = 'dbwh';
    protected $table = 'public.palet_stok';
    public $timestamps = false;
    protected $fillable = [
        'gudang_id',
        'qty_rfi',
        'qty_tbr',
        'qty_ber',
        'last_updated',
    ];

    public function gudangPalet(): BelongsTo
    {
        return $this->belongsTo(GudangPalet::class, 'gudang_id');
    }
}
