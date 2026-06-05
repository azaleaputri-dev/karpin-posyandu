<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Posyandu extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'address',
        'village',
        'contact_phone',
        'notes',
    ];

    public function children()
    {
        return $this->hasMany(Child::class);
    }

    public function devices()
    {
        return $this->hasMany(Device::class);
    }
}
