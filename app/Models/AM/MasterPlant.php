<?php

namespace App\Models\AM;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MasterPlant extends Model
{
    protected $table = 'master_plants';

    protected $fillable = [
        'customer_id',
        'city_id',
    ];

    // Relasi ke Customer
    public function customer(): BelongsTo
    {
        return $this->belongsTo(MasterCustomer::class, 'customer_id');
    }

    // Relasi ke City
    public function city(): BelongsTo
    {
        return $this->belongsTo(MasterCity::class, 'city_id');
    }
}
