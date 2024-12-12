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
        Schema::create('transfer_schedule_histories', function (Blueprint $table) {
            $table->id();
            $table->string('student_code',20);
            $table->string('from_class_code',40);
            $table->string('to_class_code',40);
            $table->foreign('student_code')->references('user_code')->on('users')
            ->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('from_class_code')->references('class_code')->on('classrooms')
            ->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('to_class_code')->references('class_code')->on('classrooms')
            ->cascadeOnDelete()->cascadeOnUpdate();
            $table->unique(['student_code', 'from_class_code', 'to_class_code'], 'unq_stdCode_frClassCode_toClass_code');
            $table->index(['student_code', 'to_class_code']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfer_schedule_histories');
    }
};
