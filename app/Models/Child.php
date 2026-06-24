<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Child extends Model
{
    use HasFactory;

    protected $fillable = [
        'posyandu_id',
        'nik',
        'rfid_uid',
        'child_name',
        'gender',
        'birth_date',
        'mother_name',
        'father_name',
        'guardian_phone',
        'address',
        'blood_type',
        'notes',
    ];

    protected $casts = [
        'birth_date' => 'date',
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
