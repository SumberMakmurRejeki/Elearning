<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trainings', function (Blueprint $table) {
            $table->id();
            $table->string('title', 150);
            $table->text('description')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft')->index();
            $table->boolean('has_pre_test')->default(false)->index();
            $table->boolean('has_post_test')->default(false)->index();
            $table->decimal('passing_grade', 5, 2)->nullable();
            $table->boolean('allow_post_test_retake')->default(false);
            $table->unsignedInteger('max_post_test_attempt')->nullable();
            $table->boolean('show_score_to_employee')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('start_date');
            $table->index('end_date');
            $table->index('created_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trainings');
    }
};
