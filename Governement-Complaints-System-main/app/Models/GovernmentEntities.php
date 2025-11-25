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

//     public function employees()
// {
//     return $this->hasMany(User::class, 'government_entity_id');
// }

   public function employees()
   {
    return $this->hasMany(Employee::class, 'government_entity_id');
   }
}
