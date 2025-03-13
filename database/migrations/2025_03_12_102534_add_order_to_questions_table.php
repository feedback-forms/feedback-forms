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
        Schema::table('questions', function (Blueprint $table) {
            // Add order field to explicitly manage question sequence within a survey
            $table->integer('order')->nullable()->after('question');

            // Add index for efficient ordering queries
            $table->index(['feedback_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            // Remove order field and index
            $table->dropIndex(['feedback_id', 'order']);
            $table->dropColumn('order');
        });
    }
};
