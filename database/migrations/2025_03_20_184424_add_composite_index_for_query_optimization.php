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
     * This migration adds a composite index to optimize queries that filter results
     * by both question_id and value_type, which are heavily used in statistics calculations.
     */
    public function up(): void
    {
        Schema::table('results', function (Blueprint $table) {
            // Check if the index already exists to avoid errors
            if (!$this->hasIndex('results', 'results_question_id_value_type_index')) {
                // Add composite index for queries that filter by both question_id and value_type
                // This optimizes the statistics calculations in StatisticsService
                $table->index(['question_id', 'value_type'], 'results_question_id_value_type_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('results', function (Blueprint $table) {
            // Drop the index if it exists
            if ($this->hasIndex('results', 'results_question_id_value_type_index')) {
                $table->dropIndex('results_question_id_value_type_index');
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