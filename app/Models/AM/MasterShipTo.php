<?php

namespace App\Models\AM;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MasterShipTo extends Model
{
    protected $table = 'master_shiptos';

    protected $fillable = [
        'customer_id',
        'city_id',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(MasterCustomer::class, 'customer_id');
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(MasterCity::class, 'city_id');
    }
}
