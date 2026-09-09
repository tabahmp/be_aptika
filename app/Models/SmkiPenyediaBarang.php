<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SmkiPenyediaBarang extends Model
{
    protected $table = 'smki_penyedia_barangs';

    protected $fillable = [
        'nama_penyedia_barang',
        'kontak_vendor',
    ];

    public function softwareStandars(): HasMany
    {
        return $this->hasMany(SmkiSoftwareStandar::class, 'penyedia_barang_id');
    }
}
