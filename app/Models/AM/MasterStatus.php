<?php

namespace App\Models\AM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterStatus extends Model
{
    use HasFactory;
    protected $table = 'master_statuses';

    protected $fillable = ['nama_status'];
}
