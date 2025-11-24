<?php

namespace App\Models\AM;

use Illuminate\Database\Eloquent\Model;

class MasterCity extends Model
{
    protected $table = 'master_cities';

    protected $fillable = [
        'city_name',
        'province',
        'nation',
    ];
}
