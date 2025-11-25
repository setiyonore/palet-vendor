<?php

namespace App\Models\AM;

use Illuminate\Database\Eloquent\Model;

class MasterJenisBarang extends Model
{
    protected $table = 'master_jenis_barangs';

    protected $fillable = ['jenis_barang'];
}
