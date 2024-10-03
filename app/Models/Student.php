<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    use HasFactory;

    protected $fillable= [
        'name',
        'level',
        'grade',
        'price',
        'fee',
        'is_active',
    ];

    // Relation to Presence (One-to-Many)
    public function presences()
    {
        return $this->hasMany(Presence::class);
    }
}
