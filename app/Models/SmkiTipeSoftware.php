<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SmkiTipeSoftware extends Model
{
    protected $table = 'smki_tipe_softwares';

    protected $fillable = [
        'nama_tipe_software',
        'keterangan',
    ];

    public function softwareStandars(): HasMany
    {
        return $this->hasMany(SmkiSoftwareStandar::class, 'tipe_software_id');
    }
}
