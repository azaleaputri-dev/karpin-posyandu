<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RfidScan extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'child_id',
        'rfid_uid',
        'status',
        'payload',
        'scanned_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'scanned_at' => 'datetime',
    ];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    public function child()
    {
        return $this->belongsTo(Child::class);
    }
}
