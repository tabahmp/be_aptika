<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmkiFormulirHardeningChecklist extends Model
{
    use HasFactory;

    protected $table = 'smki_formulir_hardening_checklists';

    protected $fillable = [
        'formulir_hardening_id',
        'kategori',
        'item_pengecekan',
        'urutan',
        'checklist',
        'keterangan',
    ];

    protected $casts = [
        'urutan' => 'integer',
    ];

    /**
     * Relasi ke Formulir Hardening induk.
     */
    public function formulir()
    {
        return $this->belongsTo(SmkiFormulirHardening::class, 'formulir_hardening_id');
    }
}
