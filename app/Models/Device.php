<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'posyandu_id',
        'device_code',
        'device_name',
        'device_type',
        'location',
        'status',
        'last_seen_at',
        'api_token',
        'api_token_hash',
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
    ];

    public function posyandu()
    {
        return $this->belongsTo(Posyandu::class);
    }

    public function measurements()
    {
        return $this->hasMany(Measurement::class);
    }

    public function rfidScans()
    {
        return $this->hasMany(RfidScan::class);
    }
}
