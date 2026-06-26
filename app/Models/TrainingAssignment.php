<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingAssignment extends Model
{
    protected $fillable = [
        'training_id',
        'target_type',
        'target_id',
        'assigned_at',
        'deadline',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'assigned_at' => 'date',
            'deadline' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function training(): BelongsTo
    {
        return $this->belongsTo(Training::class);
    }

    public function progressRecords(): HasMany
    {
        return $this->hasMany(EmployeeTrainingProgress::class, 'assignment_id');
    }
}
