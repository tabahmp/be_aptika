<?php

namespace App\Models;

use App\Traits\BelongsToBidang;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DaftarAsetTi extends Model
{
    use BelongsToBidang, SoftDeletes;

    protected $table = 'daftar_aset_tis';

    protected $fillable = [
        'bidang_id',
        'user_id',
        'kode',
        'nama_aset_id',
        'nama_aset',
        'klasifikasi_id',
        'jenis_id',
        'kategori_id',
        'no_seri',
        'merek_id',
        'tipe_id',
        'spesifikasi_id',
        'spesifikasi_teknis',
        'pemanfaatan_id',
        'pemanfaatan',
        'penyedia_id',
        'tahun_pembelian',
        'garansi',
        'date_end', // Batas Akhir Layanan Dukungan (End of Support)
        'tanggal_akhir_masa_pakai', // Batas Masa Pakai Produk (End of Life)
        'penanggung_jawab_id',
        'lokasi',
    ];

    /**
     * Relasi ke Master Nama Aset.
     */
    public function namaAsetRel(): BelongsTo
    {
        return $this->belongsTo(AsetTiNama::class, 'nama_aset_id');
    }

    /**
     * Relasi ke Klasifikasi Aset.
     */
    public function klasifikasi(): BelongsTo
    {
        return $this->belongsTo(AsetTiKlasifikasi::class, 'klasifikasi_id');
    }

    /**
     * Relasi ke Jenis Aset.
     */
    public function jenis(): BelongsTo
    {
        return $this->belongsTo(AsetTiJenis::class, 'jenis_id');
    }

    /**
     * Relasi ke Kategori Aset.
     */
    public function kategori(): BelongsTo
    {
        return $this->belongsTo(AsetTiKategori::class, 'kategori_id');
    }

    /**
     * Relasi ke Merek Aset.
     */
    public function merek(): BelongsTo
    {
        return $this->belongsTo(AsetTiMerek::class, 'merek_id');
    }

    /**
     * Relasi ke Tipe Aset.
     */
    public function tipe(): BelongsTo
    {
        return $this->belongsTo(AsetTiTipe::class, 'tipe_id');
    }

    /**
     * Relasi ke Spesifikasi Teknis Master.
     */
    public function spesifikasi(): BelongsTo
    {
        return $this->belongsTo(AsetTiSpesifikasi::class, 'spesifikasi_id');
    }

    /**
     * Relasi ke Pemanfaatan Master.
     */
    public function pemanfaatanRel(): BelongsTo
    {
        return $this->belongsTo(AsetTiPemanfaatan::class, 'pemanfaatan_id');
    }

    /**
     * Relasi ke Penyedia Master.
     */
    public function penyedia(): BelongsTo
    {
        return $this->belongsTo(AsetTiPenyedia::class, 'penyedia_id');
    }

    /**
     * Relasi ke Penanggung Jawab Master.
     */
    public function penanggungJawab(): BelongsTo
    {
        return $this->belongsTo(AsetTiPenanggungJawab::class, 'penanggung_jawab_id');
    }

    /**
     * Relasi ke User pembuat/penginput data.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
