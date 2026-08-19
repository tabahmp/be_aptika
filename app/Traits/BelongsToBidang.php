<?php

namespace App\Traits;

use App\Models\Bidang;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Trait BelongsToBidang
 *
 * Menambahkan data isolation otomatis pada Model transaksi.
 * - Global Scope: Otomatis filter WHERE bidang_id = auth()->user()->bidang_id
 * - Creating Event: Otomatis set bidang_id dari user yang sedang login
 * - Relasi: belongsTo Bidang
 *
 * Admin Aptika dikecualikan dari Global Scope agar dapat melihat data seluruh bidang.
 */
trait BelongsToBidang
{
    protected static function bootBelongsToBidang(): void
    {
        // 1. Global Scope: Otomatis tambahkan WHERE bidang_id = auth()->user()->bidang_id
        //    Admin Aptika dikecualikan agar bisa melihat data semua bidang
        static::addGlobalScope('bidang_scope', function (Builder $builder) {
            if (auth()->check() && !auth()->user()->isAdminAptika()) {
                $builder->where(
                    $builder->getModel()->getTable() . '.bidang_id',
                    auth()->user()->bidang_id
                );
            }
        });

        // 2. Creating Event: Otomatis set bidang_id dari auth user saat menyimpan record baru
        static::creating(function (Model $model) {
            if (auth()->check() && empty($model->bidang_id)) {
                $model->bidang_id = auth()->user()->bidang_id;
            }
        });
    }

    /**
     * Relasi ke tabel bidangs.
     */
    public function bidang()
    {
        return $this->belongsTo(Bidang::class, 'bidang_id');
    }
}
