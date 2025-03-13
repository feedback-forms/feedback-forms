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
        // Check if the table exists first
        if (Schema::hasTable('response_values')) {
            // Drop the foreign key constraint first if it exists
            Schema::table('response_values', function (Blueprint $table) {
                if (Schema::hasColumn('response_values', 'result_id')) {
                    $table->dropForeign(['result_id']);
                }
            });

            Schema::dropIfExists('response_values');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
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
};
