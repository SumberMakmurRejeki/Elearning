<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_id')->constrained()->cascadeOnDelete();
            $table->enum('target_type', ['employee', 'division', 'position']);
            $table->unsignedBigInteger('target_id');
            $table->date('assigned_at');
            $table->date('deadline')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('training_id');
            $table->index(['target_type', 'target_id']);
            $table->index('assigned_at');
            $table->index('deadline');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_assignments');
    }
};
