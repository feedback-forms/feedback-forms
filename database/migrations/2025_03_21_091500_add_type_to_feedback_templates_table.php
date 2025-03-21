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
        Schema::table('feedback_templates', function (Blueprint $table) {
            $table->string('type')->after('name')->nullable();
        });

        // Update existing records based on name patterns
        $this->updateExistingTemplates();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('feedback_templates', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }

    /**
     * Update existing feedback templates with appropriate types
     * based on their current names
     */
    private function updateExistingTemplates(): void
    {
        // Update templates with 'smiley' in the name
        DB::table('feedback_templates')
            ->where('name', 'like', '%templates.feedback.smiley')
            ->update(['type' => 'smiley']);

        // Update templates with 'target' in the name
        DB::table('feedback_templates')
            ->where('name', 'like', '%templates.feedback.target')
            ->update(['type' => 'target']);

        // Update templates with 'table' in the name
        DB::table('feedback_templates')
            ->where('name', 'like', '%templates.feedback.table')
            ->update(['type' => 'table']);

        // Any templates not matched by the above patterns will remain with null type
        // and will continue to be handled by the DefaultTemplateStrategy
    }
};