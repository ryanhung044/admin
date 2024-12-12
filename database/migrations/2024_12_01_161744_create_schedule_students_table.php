<?php

use App\Models\Schedule;
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
        Schema::create('schedule_student', function (Blueprint $table) {
            $table->foreignIdFor(Schedule::class)->constrained()
            ->cascadeOnDelete()->cascadeOnUpdate();

        $table->string('student_code', 20)->comment('Mã sinh viên');
        $table->foreign('student_code')->references('user_code')
            ->on('users')->cascadeOnDelete()->cascadeOnUpdate();

        $table->primary(['schedule_id', 'student_code']);
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_students');
    }
};
