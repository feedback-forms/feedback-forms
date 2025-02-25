<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\{User, Feedback_template};

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class);
            $table->foreignIdFor(Feedback_template::class);
            $table->string('accesskey', 8);
            $table->integer('limit');
            $table->integer('already_answered');
            $table->date('expire_date');
            $table->string('school_year')->nullable();
            $table->string('department')->nullable();
            $table->string('grade_level')->nullable();
            $table->string('class')->nullable();
            $table->string('subject')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedback');
    }
};
