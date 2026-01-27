<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Asset;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'employee_id',
        'loan_type',
        'start_date',
        'due_date',
        'status',
        'reason',
        'note',
    ];

    protected $casts = [
        'start_date' => 'date',
        'due_date' => 'date',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
