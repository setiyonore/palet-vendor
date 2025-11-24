<?php

namespace App\Models\AM;

use Illuminate\Database\Eloquent\Model;

class MasterCustomer extends Model
{
    protected $table = 'master_customers';

    protected $fillable = [
        'kode',
        'nama',
        'alamat',
        'telepon',
    ];
}
