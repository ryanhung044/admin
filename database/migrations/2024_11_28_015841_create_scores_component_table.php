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
        Schema::create('scores_component', function (Blueprint $table) {
            $table->id();
            $table->string('student_code',20);
            $table->foreign('student_code')->references('user_code')->on('users')
            ->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('class_code', 40)->comment('Mã lớp học');
            $table->foreign('class_code')->references('class_code')->on('classrooms')
            ->cascadeOnDelete()->cascadeOnUpdate();
            $table->decimal('score', 5, 2);
            $table->string('assessment_code', 40);
            $table->foreign('assessment_code')->references('assessment_code')->on('assessment_items')
            ->cascadeOnDelete()->cascadeOnUpdate();;
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scores_component');
    }
};
