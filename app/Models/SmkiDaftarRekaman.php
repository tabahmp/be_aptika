<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SmkiDaftarRekaman extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'smki_daftar_rekamans';

    protected $fillable = [
        'user_id',
        'bidang_id',
        'judul',
        'klasifikasi_id',
        'retensi_id',
        'pemilik_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bidang()
    {
        return $this->belongsTo(Bidang::class);
    }

    public function klasifikasi()
    {
        return $this->belongsTo(SmkiRekamanKlasifikasi::class, 'klasifikasi_id');
    }

    public function retensi()
    {
        return $this->belongsTo(SmkiRekamanRetensi::class, 'retensi_id');
    }

    public function pemilik()
    {
        return $this->belongsTo(SmkiRekamanPemilik::class, 'pemilik_id');
    }
}
