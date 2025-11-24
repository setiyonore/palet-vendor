<?php

namespace App\Models\AM;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MasterArmada extends Model
{
    protected $table = 'master_armadas';

    protected $fillable = [
        'tipe_armada_id',
        'nomor_pintu',
        'nomor_polisi',
        'driver_id',
    ];

    // Relasi ke Tipe Armada
    public function tipeArmada(): BelongsTo
    {
        return $this->belongsTo(MasterTipeArmada::class, 'tipe_armada_id');
    }

    // Relasi ke Driver (Asumsi nama modelnya Driver atau MasterDriver di folder AM)
    public function driver(): BelongsTo
    {
        // Sesuaikan dengan nama Class Model Driver Anda yang sebenarnya
        return $this->belongsTo(Driver::class, 'driver_id');
    }
}
