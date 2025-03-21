<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration adds an index to improve the performance of submission-related queries
     * which frequently join results and questions tables and filter by feedback_id.
     */
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            // Check if the index already exists to avoid errors
            if (!$this->hasIndex('questions', 'questions_id_feedback_id_index')) {
                // Add composite index for questions.id and questions.feedback_id
                // This optimizes joins with results table and filtering by feedback_id
                // which is a common pattern in getSubmissionsBaseQuery() used for statistics
                $table->index(['id', 'feedback_id'], 'questions_id_feedback_id_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            // Drop the index if it exists
            if ($this->hasIndex('questions', 'questions_id_feedback_id_index')) {
                $table->dropIndex('questions_id_feedback_id_index');
            }
        });
    }

    /**
     * Check if an index exists for a table
     */
    private function hasIndex(string $tableName, string $indexName): bool
    {
        // For PostgreSQL
        if (DB::connection()->getDriverName() === 'pgsql') {
            return DB::select("SELECT to_regclass('public.{$indexName}') as index_exists")[0]->index_exists !== null;
        }

        // For MySQL and others
        return Schema::hasTable($tableName) &&
               count(DB::select("SHOW INDEXES FROM {$tableName} WHERE Key_name = '{$indexName}'")) > 0;
    }
};