<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('response_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('result_id')->constrained()->cascadeOnDelete();
            $table->string('question_template_type')->nullable()->index();
            $table->integer('range_value')->nullable();
            $table->text('text_value')->nullable();
            $table->json('json_value')->nullable();
            $table->timestamps();

            $table->index(['question_template_type', 'range_value'], 'response_values_range_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('response_values');
    }
};
