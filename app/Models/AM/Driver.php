<?php

namespace App\Models\AM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    use HasFactory;
    protected $table = 'master_drivers';
    protected $fillable = [
        'nama',
        'no_hp'
    ];
}
