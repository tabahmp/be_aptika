<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsetTiPenyedia extends Model
{
    protected $table = 'aset_ti_penyedias';
    protected $fillable = ['nama_penyedia'];
}
