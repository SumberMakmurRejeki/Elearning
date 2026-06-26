<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeTrainingProgress extends Model
{
    protected $table = 'employee_training_progress';

    protected $fillable = [
        'employee_id',
        'training_id',
        'assignment_id',
        'status',
        'pre_test_completed_at',
        'material_completed_at',
        'post_test_completed_at',
        'final_score',
        'final_status',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'pre_test_completed_at' => 'datetime',
            'material_completed_at' => 'datetime',
            'post_test_completed_at' => 'datetime',
            'final_score' => 'decimal:2',
            'completed_at' => 'datetime',
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

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(TrainingAssignment::class, 'assignment_id');
    }
}
