<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $table = 'services';

    protected $fillable = [
        'parent_id',
        'code',
        'name',
        'description',
    ];

    public function parent()
    {
        return $this->belongsTo(Service::class, 'parent_id');
    }

    public function subServices()
    {
        return $this->hasMany(Service::class, 'parent_id');
    }

    public function bidangs()
    {
        return $this->belongsToMany(Bidang::class, 'bidang_services', 'service_id', 'bidang_id')
                    ->withPivot('is_enabled')
                    ->withTimestamps();
    }
}
