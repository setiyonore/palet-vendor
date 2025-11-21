<?php

namespace App\Models\AM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterTipeArmada extends Model
{
    use HasFactory;
    protected $table = 'public.master_tipe_armada';

    protected $fillable = [
        'kode_tipe',
        'nama_tipe',
    ];
}
