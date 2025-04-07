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
            // Remove the already_answered column as it's redundant
            // This field is calculated dynamically via getSubmissionCountAttribute()
            // and accessed through the getAlreadyAnsweredAttribute() method
            $table->dropColumn('already_answered');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('feedback', function (Blueprint $table) {
            // Add back the already_answered column if migration is rolled back
            $table->integer('already_answered')->after('limit')->default(0);
        });
    }
};
