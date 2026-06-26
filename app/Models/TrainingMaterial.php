<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingMaterial extends Model
{
    protected $fillable = [
        'training_id',
        'title',
        'description',
        'material_type',
        'file_path',
        'url',
        'file_type',
        'file_size',
        'order_number',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'order_number' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function training(): BelongsTo
    {
        return $this->belongsTo(Training::class);
    }
}
