<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $table = 'services';

    protected $fillable = [
        'code',
        'name',
        'description',
    ];

    public function bidangs()
    {
        return $this->belongsToMany(Bidang::class, 'bidang_services', 'service_id', 'bidang_id')
                    ->withPivot('is_enabled')
                    ->withTimestamps();
    }
}
