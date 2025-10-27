<?php

namespace App\Models\PM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GudangPalet extends Model
{
    use HasFactory;
    // protected $connection = 'dbwh';
    protected $table = 'public.gudang_palet';
    protected $guarded = [];
}
