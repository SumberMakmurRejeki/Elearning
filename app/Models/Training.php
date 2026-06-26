<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Training extends Model
{
    protected $fillable = [
        'title',
        'description',
        'start_date',
        'end_date',
        'status',
        'has_pre_test',
        'has_post_test',
        'passing_grade',
        'allow_post_test_retake',
        'max_post_test_attempt',
        'show_score_to_employee',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'has_pre_test' => 'boolean',
            'has_post_test' => 'boolean',
            'passing_grade' => 'decimal:2',
            'allow_post_test_retake' => 'boolean',
            'max_post_test_attempt' => 'integer',
            'show_score_to_employee' => 'boolean',
        ];
    }

    public function materials(): HasMany
    {
        return $this->hasMany(TrainingMaterial::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(TrainingAssignment::class);
    }

    public function progressRecords(): HasMany
    {
        return $this->hasMany(EmployeeTrainingProgress::class);
    }
}
