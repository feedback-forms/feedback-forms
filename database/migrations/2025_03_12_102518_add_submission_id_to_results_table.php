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
        Schema::table('results', function (Blueprint $table) {
            // Add submission_id as UUID to group results from a single submission (nullable initially)
            $table->uuid('submission_id')->nullable()->after('question_id');

            // Add index for performance optimization on common queries
            $table->index('submission_id');

            // Add value_type column to explicitly indicate the data type of rating_value
            $table->string('value_type')->default('text')->after('submission_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('results', function (Blueprint $table) {
            // Remove the columns and index
            $table->dropIndex(['submission_id']);
            $table->dropColumn('submission_id');
            $table->dropColumn('value_type');
        });
    }
};
