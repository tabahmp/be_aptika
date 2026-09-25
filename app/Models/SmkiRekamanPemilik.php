<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmkiRekamanPemilik extends Model
{
    use HasFactory;

    protected $table = 'smki_rekaman_pemiliks';

    protected $fillable = [
        'nama_pemilik',
    ];

    public function daftarRekaman()
    {
        return $this->hasMany(SmkiDaftarRekaman::class, 'pemilik_id');
    }
}
