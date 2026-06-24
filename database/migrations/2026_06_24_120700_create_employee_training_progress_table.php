<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_training_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('training_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assignment_id')->nullable()->constrained('training_assignments')->nullOnDelete();
            $table->string('status', 50)->default('not_started')->index();
            $table->timestamp('pre_test_completed_at')->nullable();
            $table->timestamp('material_completed_at')->nullable();
            $table->timestamp('post_test_completed_at')->nullable();
            $table->decimal('final_score', 5, 2)->nullable();
            $table->string('final_status', 50)->nullable()->index();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'training_id']);
            $table->index('employee_id');
            $table->index('training_id');
            $table->index('assignment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_training_progress');
    }
};
