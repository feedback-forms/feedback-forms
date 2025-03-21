<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Models\{FeedbackTemplate, QuestionTemplate, Feedback};

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(FeedbackTemplate::class);
            $table->foreignIdFor(QuestionTemplate::class);
            $table->foreignIdFor(Feedback::class)->nullable();
            $table->string('question');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
