<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SmkiFormulirHardening extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'smki_formulir_hardenings';

    protected $fillable = [
        'no_dokumen',
        'no_revisi',
        'tanggal_terbit',
        'aset_id',
        'nomor_aset',
        'jenis_aset',
        'merek_tipe',
        'lokasi',
        'tanggal_check',
        'status',
        'kota',
        'tanggal_pengesahan',
        'nama_auditor',
        'nip_auditor',
        'jabatan_auditor',
        'compliance_rate',
        'items_passed',
        'items_failed',
        'bidang_id',
        'user_id',
    ];

    protected $casts = [
        'tanggal_terbit'     => 'date:Y-m-d',
        'tanggal_check'      => 'date:Y-m-d',
        'tanggal_pengesahan' => 'date:Y-m-d',
        'compliance_rate'    => 'float',
        'items_passed'       => 'integer',
        'items_failed'       => 'integer',
    ];

    /**
     * Relasi ke item checklist hardening.
     */
    public function checklists()
    {
        return $this->hasMany(SmkiFormulirHardeningChecklist::class, 'formulir_hardening_id')
            ->orderBy('urutan', 'asc');
    }

    /**
     * Relasi opsional ke master daftar aset TI.
     */
    public function aset()
    {
        return $this->belongsTo(DaftarAsetTi::class, 'aset_id');
    }

    /**
     * Relasi ke Bidang (multi-tenancy).
     */
    public function bidang()
    {
        return $this->belongsTo(Bidang::class, 'bidang_id');
    }

    /**
     * Relasi ke User pembuat/pengelola.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Helper untuk menghitung ulang persentase kepatuhan (compliance rate).
     */
    public function recalculateCompliance(): void
    {
        $total = $this->checklists()->count();
        $passed = $this->checklists()->where('checklist', 'Pass')->count();
        $failed = $this->checklists()->where('checklist', 'Fail')->count();

        $rate = $total > 0 ? round(($passed / $total) * 100, 2) : 0;

        $this->updateQuietly([
            'items_passed'    => $passed,
            'items_failed'    => $failed,
            'compliance_rate' => $rate,
        ]);
    }
}
