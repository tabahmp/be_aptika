<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bidang extends Model
{
    use HasFactory;

    protected $table = 'bidangs';

    protected $fillable = [
        'code',
        'name',
        'description',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'bidang_id');
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'bidang_services', 'bidang_id', 'service_id')
                    ->withPivot('is_enabled')
                    ->withTimestamps();
    }
}
