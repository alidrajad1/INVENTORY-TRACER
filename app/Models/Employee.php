<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'position',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }

    function histories(): HasMany
    {
        return $this->hasMany(AssetHistory::class);
    }
}
