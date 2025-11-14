<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GovernmentEntities extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'location'
    ];

    protected $casts = [
        'location' => 'array'
    ];
}
