<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmkiRekamanKlasifikasi extends Model
{
    use HasFactory;

    protected $table = 'smki_rekaman_klasifikasis';

    protected $fillable = [
        'nama_klasifikasi',
    ];

    public function daftarRekaman()
    {
        return $this->hasMany(SmkiDaftarRekaman::class, 'klasifikasi_id');
    }
}
