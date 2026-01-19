<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Software extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'publisher',
        'is_blacklisted'
    ];

    protected $casts = [
        'is_blacklisted' => 'boolean',
    ];

    public function assets(): BelongsToMany
    {
        return $this->belongsToMany(Asset::class, 'asset_software')
                    ->withPivot('version', 'installed_at')
                    ->withTimestamps();
    }
}
