<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SmkiKategori extends Model
{
    protected $table = 'smki_kategoris';

    protected $fillable = [
        'nama_kategori',
        'keterangan',
    ];

    public function softwareStandars(): HasMany
    {
        return $this->hasMany(SmkiSoftwareStandar::class, 'kategori_id');
    }
}
