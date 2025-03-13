<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For PostgreSQL, we need to use a raw SQL statement to cast the column
        DB::statement('ALTER TABLE results ALTER COLUMN value TYPE JSONB USING value::jsonb');

        Schema::table('results', function (Blueprint $table) {
            // Add submission_id to group results from the same submission
            $table->uuid('submission_id')->nullable()->index()->after('question_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('results', function (Blueprint $table) {
            // Remove the submission_id column
            $table->dropColumn('submission_id');
        });

        // Revert value column back to string
        DB::statement('ALTER TABLE results ALTER COLUMN value TYPE VARCHAR(255) USING value::text');
    }
};
