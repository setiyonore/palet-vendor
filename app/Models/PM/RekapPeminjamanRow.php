<?php

namespace App\Models\PM;

use Illuminate\Database\Eloquent\Model;

class RekapPeminjamanRow extends Model
{
    protected $connection = 'dbwh';    // samakan dengan koneksi yang dipakai di log error
    protected $table = 'peminjaman_aktif'; // alias subquery
    public $incrementing = false;
    public $timestamps = false;
    protected $guarded = [];
}
