<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmkiRekamanRetensi extends Model
{
    use HasFactory;

    protected $table = 'smki_rekaman_retensis';

    protected $fillable = [
        'nama_retensi',
    ];

    public function daftarRekaman()
    {
        return $this->hasMany(SmkiDaftarRekaman::class, 'retensi_id');
    }
}
