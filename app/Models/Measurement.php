<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Measurement extends Model
{
    use HasFactory;

    protected $fillable = [
        'child_id',
        'device_id',
        'measured_at',
        'weight_kg',
        'height_cm',
        'temperature_c',
        'source',
        'notes',
    ];

    protected $casts = [
        'measured_at' => 'datetime',
        'weight_kg' => 'decimal:2',
        'height_cm' => 'decimal:2',
        'temperature_c' => 'decimal:2',
    ];

    public function child()
    {
        return $this->belongsTo(Child::class);
    }

    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}
