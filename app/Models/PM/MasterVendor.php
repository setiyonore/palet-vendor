<?php

namespace App\Models\PM;

use Illuminate\Database\Eloquent\Model;

class MasterVendor extends Model
{
    /**
     * Menentukan koneksi database ke data warehouse.
     */
    protected $connection = 'dbwh';

    /**
     * Menentukan nama tabel di database.
     */
    protected $table = 'public.master_vendor';

    /**
     * Mengizinkan semua kolom untuk diisi (mass assignment).
     */
    protected $guarded = [];
}
