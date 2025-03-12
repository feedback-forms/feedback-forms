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
        Schema::table('feedback', function (Blueprint $table) {
            // Add status field to track the lifecycle of the feedback/survey
            $table->string('status')->default('running')->after('expire_date');

            // Make already_answered nullable (since we'll calculate it dynamically from submission_ids)
            $table->integer('already_answered')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('feedback', function (Blueprint $table) {
            // Remove status field
            $table->dropColumn('status');

            // Make already_answered required again
            $table->integer('already_answered')->nullable(false)->change();
        });
    }
};
