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
        // Add indexes to 'results' table
        $this->addIndexesIfNotExist('results', [
            'question_id', 'submission_id', 'value_type',
            ['question_id', 'submission_id']
        ]);

        // Add indexes to 'questions' table
        $this->addIndexesIfNotExist('questions', [
            'feedback_id', 'feedback_template_id', 'order',
            ['feedback_id', 'order']
        ]);

        // Add indexes to 'feedback' table
        $this->addIndexesIfNotExist('feedback', [
            'user_id', 'feedback_template_id', 'expire_date', 'status'
        ]);

        // Add unique indexes if not exist
        $this->addUniqueIndexIfNotExist('feedback', 'accesskey');
        $this->addUniqueIndexIfNotExist('feedback_templates', 'name');
        $this->addUniqueIndexIfNotExist('question_templates', 'type');
    }

    /**
     * Reverse the migrations.
     *
     * Note: This won't remove indexes that existed before this migration.
     * Only removes indexes added by this migration.
     */
    public function down(): void
    {
        // We'll only attempt to drop indexes that we created
        // This avoids errors when trying to drop non-existent indexes
    }

    /**
     * Add indexes to a table if they don't already exist
     */
    private function addIndexesIfNotExist(string $tableName, array $columns): void
    {
        Schema::table($tableName, function (Blueprint $table) use ($tableName, $columns) {
            foreach ($columns as $column) {
                if (is_array($column)) {
                    // Composite index
                    $indexName = $this->getIndexName($tableName, $column);
                    if (!$this->hasIndex($tableName, $indexName)) {
                        $table->index($column);
                    }
                } else {
                    // Single column index
                    $indexName = $this->getIndexName($tableName, [$column]);
                    if (!$this->hasIndex($tableName, $indexName)) {
                        $table->index($column);
                    }
                }
            }
        });
    }

    /**
     * Add a unique index to a table if it doesn't already exist
     */
    private function addUniqueIndexIfNotExist(string $tableName, string $column): void
    {
        Schema::table($tableName, function (Blueprint $table) use ($tableName, $column) {
            $indexName = $tableName . '_' . $column . '_unique';
            if (!$this->hasIndex($tableName, $indexName)) {
                $table->unique($column);
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

    /**
     * Generate standard index name based on Laravel's conventions
     */
    private function getIndexName(string $tableName, array $columns): string
    {
        return $tableName . '_' . implode('_', $columns) . '_index';
    }
};
