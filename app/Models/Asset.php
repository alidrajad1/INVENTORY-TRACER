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

    protected $guarded = ['id'];

    protected $casts = [
        'purchase_date' => 'date',
        'last_seen_at' => 'datetime',
        'hardware_specs' => 'array'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Cek apakah asset_tag kosong? Jika ya, kita buatkan.
            if (empty($model->asset_tag)) {
                
                // 1. Tentukan Tahun saat ini
                $year = date('Y'); // Contoh: 2024
                
                // 2. Tentukan Format Prefix
                // Format: AST-2024-
                $prefix = 'AST-' . $year . '-';
                
                // 3. Cari aset terakhir yang punya prefix SAMA (tahun yang sama)
                $latestAsset = self::where('asset_tag', 'like', $prefix . '%')
                                   ->orderBy('id', 'desc')
                                   ->first();

                if (!$latestAsset) {
                    // Jika belum ada aset di tahun ini, mulai dari 1
                    $number = 1;
                } else {
                    // Jika sudah ada, kita ambil nomor urutnya
                    // Contoh: AST-2024-00005. Kita buang prefix "AST-2024-", sisa "00005"
                    // Lalu ubah jadi integer: 5
                    $lastNumber = (int) substr($latestAsset->asset_tag, strlen($prefix));
                    
                    // Tambah 1
                    $number = $lastNumber + 1;
                }

                // 4. Gabungkan Format Akhir
                // str_pad berguna untuk menambah nol di depan (00001)
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

    public function software(): BelongsToMany
    {
        return $this->belongsToMany(Software::class, 'asset_software')
                    ->withPivot('version', 'installed_at')
                    ->withTimestamps();
    }

    public function histories(): HasMany
    {
        return $this->hasMany(AssetHistory::class);
    }

    public function maintenance(): HasMany
    {
        return $this->hasMany(Maintenance::class);
    }
}
