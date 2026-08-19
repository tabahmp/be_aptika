<?php

namespace App\Models;

use App\Traits\BelongsToBidang;
use Illuminate\Database\Eloquent\Model;

class Kerentanan extends Model
{
    use BelongsToBidang;
    protected $table = 'kerentanans';

    protected $fillable = [
        'nomor_surat',
        'tanggal',
        'aplikasi',
        'url',
        'tingkat_kerentanan',
        'perihal',
        'deskripsi',
        'isi_lampiran',
        'rekomendasi',
        'lampiran',
        'lampiran_nama',
        'status',
        'bidang_id',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public static function generateNomorSurat(): string
    {
        $year = now()->year;
        $prefix = "VULN-{$year}-";

        $last = static::where('nomor_surat', 'like', "{$prefix}%")
            ->orderByRaw('CAST(SUBSTRING(nomor_surat, ' . (strlen($prefix) + 1) . ') AS UNSIGNED) DESC')
            ->first();

        $nextNumber = $last ? ((int) substr($last->nomor_surat, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }
}

