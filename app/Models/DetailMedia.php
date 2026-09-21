<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailMedia extends Model
{
    use HasFactory;

    protected $table = 'detail_media';
    protected $primaryKey = 'id_detail';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'id_pelaksanaan',
        'no_urut',
        'nama_perangkat',
        'spesifikasi',
        'jenis_media',
        'serial_number',
        'jumlah',
        'satuan',
        'keterangan',
    ];

    protected $casts = [
        'no_urut'        => 'integer',
        'jumlah'         => 'integer',
        'id_pelaksanaan' => 'integer',
        'created_at'     => 'datetime',
        'updated_at'     => 'datetime',
    ];

    protected $appends = [
        'spesifikasi_serial_display',
    ];

    /**
     * Relasi balik ke Berita Acara induk.
     */
    public function beritaAcara(): BelongsTo
    {
        return $this->belongsTo(BeritaAcara::class, 'id_pelaksanaan', 'id_ba');
    }

    /**
     * Format tampilan gabungan spesifikasi dan nomor seri sesuai standar FR-014.
     */
    public function getSpesifikasiSerialDisplayAttribute(): string
    {
        $parts = [];
        if (!empty($this->spesifikasi)) {
            $parts[] = $this->spesifikasi;
        }
        if (!empty($this->serial_number)) {
            $parts[] = "S/N: " . $this->serial_number;
        }
        return count($parts) > 0 ? implode(' / ', $parts) : '-';
    }
}
