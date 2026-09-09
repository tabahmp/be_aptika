<?php

namespace App\Models;

use App\Traits\BelongsToBidang;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SmkiSoftwareStandar extends Model
{
    use BelongsToBidang, SoftDeletes;

    protected $table = 'smki_software_standars';

    protected $fillable = [
        'bidang_id',
        'user_id',
        'nomor_kelompok',
        'nama_software',
        'tipe_software_id',
        'versi',
        'penyedia_barang_id',
        'kategori_id',
        'keterangan',
    ];

    /**
     * Relasi ke Kategori Software.
     */
    public function kategori(): BelongsTo
    {
        return $this->belongsTo(SmkiKategori::class, 'kategori_id');
    }

    /**
     * Relasi ke Tipe Software.
     */
    public function tipeSoftware(): BelongsTo
    {
        return $this->belongsTo(SmkiTipeSoftware::class, 'tipe_software_id');
    }

    /**
     * Relasi ke Penyedia Barang / Vendor.
     */
    public function penyediaBarang(): BelongsTo
    {
        return $this->belongsTo(SmkiPenyediaBarang::class, 'penyedia_barang_id');
    }

    /**
     * Relasi ke User pembuat/penginput data.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
