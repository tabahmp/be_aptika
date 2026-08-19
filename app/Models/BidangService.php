<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BidangService extends Model
{
    use HasFactory;

    protected $table = 'bidang_services';

    protected $fillable = [
        'bidang_id',
        'service_id',
        'is_enabled',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];

    public function bidang()
    {
        return $this->belongsTo(Bidang::class, 'bidang_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }
}
