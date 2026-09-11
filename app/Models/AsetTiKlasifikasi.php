<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsetTiKlasifikasi extends Model
{
    protected $table = 'aset_ti_klasifikasis';
    protected $fillable = ['nama_klasifikasi'];
}
