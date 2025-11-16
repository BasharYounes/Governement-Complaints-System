<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GovernmentEntities extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'code',
        'location'
    ];

    protected $casts = [
        'location' => 'array'
    ];
}
