<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Asset extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_tag',
        'serial_number',

        'name',
        'brand',
        'model',
        'hardware_specs',
        'purchase_year',
        'period',
        'vendor',

        'category_id',
        'location_id',
        'employee_id',

        'status',
        'loan_type',
        'last_audit_date',
        'due_date',
        'condition',
    ];

    protected $casts = [
        'purchase_date' => 'integer',
        'period' => 'integer',
        'due_date' => 'date',
        'last_audit_date' => 'datetime',
        'last_seen_at' => 'datetime',
        'hardware_specs' => 'array'
    ];

    protected $appends = [
        'expiry_year',
        'is_assets_active',
    ];

    public function getExpiryYearAttribute()
    {
        if (!$this->purchase_year || !$this->period) {
            return null;
        }
        return $this->purchase_year + $this->period;
    }

    public function getIsAssetsActiveAttribute()
    {
        if (!$this->expiry_year)
            return false;
        return date('Y') <= $this->expiry_year;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->asset_tag)) {

                $year = date('Y');

                $prefix = 'AST-' . $year . '-';

                $latestAsset = self::where('asset_tag', 'like', $prefix . '%')
                    ->orderBy('id', 'desc')
                    ->first();

                if (!$latestAsset) {
                    $number = 1;
                } else {

                    $lastNumber = (int) substr($latestAsset->asset_tag, strlen($prefix));

                    $number = $lastNumber + 1;
                }

                $model->asset_tag = $prefix . str_pad($number, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(AssetHistory::class);
    }

    public function maintenance(): HasMany
    {
        return $this->hasMany(Maintenance::class);
    }

    public function loanRequest(): HasMany
    {
        return $this->hasMany(LoanRequest::class);
    }
}
