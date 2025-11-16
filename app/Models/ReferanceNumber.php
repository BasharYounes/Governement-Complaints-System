<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferanceNumber extends Model
{
    protected $fillable = [
        'year',
        'gov_code',
        'counter'
    ];

    
}
