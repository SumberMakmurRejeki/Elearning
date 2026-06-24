<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_id')->constrained()->cascadeOnDelete();
            $table->enum('test_type', ['pre_test', 'post_test'])->index();
            $table->enum('question_type', ['multiple_choice', 'essay'])->index();
            $table->unsignedInteger('order_number');
            $table->text('question_text');
            $table->decimal('weight', 5, 2);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->unique(['training_id', 'test_type', 'order_number']);
            $table->index('training_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
