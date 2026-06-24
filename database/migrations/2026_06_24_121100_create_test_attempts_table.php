<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('test_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('training_id')->constrained()->cascadeOnDelete();
            $table->enum('test_type', ['pre_test', 'post_test'])->index();
            $table->unsignedInteger('attempt_number');
            $table->string('status', 50)->default('in_progress')->index();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->decimal('mcq_score', 5, 2)->default(0);
            $table->decimal('essay_score', 5, 2)->default(0);
            $table->decimal('final_score', 5, 2)->nullable();
            $table->string('grading_status', 50)->default('auto_graded')->index();
            $table->string('pass_status', 50)->nullable()->index();
            $table->timestamps();

            $table->unique(['employee_id', 'training_id', 'test_type', 'attempt_number'], 'ta_employee_training_type_attempt_unique');
            $table->index(['employee_id', 'training_id', 'test_type'], 'ta_employee_training_type_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_attempts');
    }
};
