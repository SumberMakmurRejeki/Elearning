<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_access_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('training_id')->constrained()->cascadeOnDelete();
            $table->foreignId('material_id')->constrained('training_materials')->cascadeOnDelete();
            $table->timestamp('opened_at');
            $table->timestamps();

            $table->unique(['employee_id', 'material_id']);
            $table->index(['employee_id', 'training_id']);
            $table->index('material_id');
            $table->index('opened_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_access_logs');
    }
};
