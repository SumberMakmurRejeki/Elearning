<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TestAttempt extends Model
{
    protected $fillable = [
        'employee_id',
        'training_id',
        'test_type',
        'attempt_number',
        'status',
        'started_at',
        'submitted_at',
        'mcq_score',
        'essay_score',
        'final_score',
        'grading_status',
        'pass_status',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'submitted_at' => 'datetime',
            'mcq_score' => 'decimal:2',
            'essay_score' => 'decimal:2',
            'final_score' => 'decimal:2',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function training(): BelongsTo
    {
        return $this->belongsTo(Training::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(TestAnswer::class, 'attempt_id');
    }
}
