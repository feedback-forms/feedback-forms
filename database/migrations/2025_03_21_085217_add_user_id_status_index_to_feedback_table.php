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
     * This migration adds a composite index to optimize queries that filter feedback
     * by both user_id and status, which are frequently used in administrator dashboards
     * and reporting features.
     */
    public function up(): void
    {
        Schema::table('feedback', function (Blueprint $table) {
            // Check if the index already exists to avoid errors
            if (!$this->hasIndex('feedback', 'feedback_user_id_status_index')) {
                // Add composite index for queries that filter by both user_id and status
                // This optimizes dashboard queries and reports filtering by user and status
                $table->index(['user_id', 'status'], 'feedback_user_id_status_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('feedback', function (Blueprint $table) {
            // Drop the index if it exists
            if ($this->hasIndex('feedback', 'feedback_user_id_status_index')) {
                $table->dropIndex('feedback_user_id_status_index');
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
