<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    protected $fillable = [
        'training_id',
        'test_type',
        'question_type',
        'order_number',
        'question_text',
        'weight',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'order_number' => 'integer',
            'weight' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function training(): BelongsTo
    {
        return $this->belongsTo(Training::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class);
    }
}
