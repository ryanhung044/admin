<?php

use App\Models\Category;
use App\Models\Classroom;
use App\Models\Room;
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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->string('class_code', 40)->comment('Mã lớp học');
            $table->foreign('class_code')->references('class_code')->on('classrooms')
                ->cascadeOnDelete()->cascadeOnUpdate();

            $table->string('room_code', 40)->comment('Mã phòng học');
            $table->foreign('room_code')->references('cate_code')->on('categories')
                ->restrictOnDelete()->restrictOnUpdate();

            $table->string('session_code', 40)->comment('Mã ca học');
            $table->foreign('session_code')->references('cate_code')->on('categories')
                ->restrictOnDelete()->restrictOnUpdate();

            $table->string('teacher_code', 20)->nullable()->comment('Giảng viên');
            $table->foreign('teacher_code')->references('user_code')
                ->on('users')->restrictOnDelete()->cascadeOnUpdate();

            $table->date('date');

            $table->enum('type', ['study', 'exam'])->default('study');
            $table->unique(['class_code', 'room_code', 'session_code', 'date', 'type']);

            $table->unique(['room_code', 'session_code', 'teacher_code', 'date' , 'type']);
            $table->index('date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
