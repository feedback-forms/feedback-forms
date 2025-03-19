<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\{Department, GradeLevel, SchoolClass, SchoolYear, Subject, User, Feedback_template};

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
            $table->string('accesskey', 9);
            $table->integer('limit');
            $table->integer('already_answered');
            $table->date('expire_date');
            $table->foreignIdFor(SchoolYear::class)->nullable();
            $table->foreignIdFor(Department::class)->nullable();
            $table->foreignIdFor(GradeLevel::class)->nullable();
            $table->foreignIdFor(SchoolClass::class)->nullable();
            $table->foreignIdFor(Subject::class)->nullable();
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
