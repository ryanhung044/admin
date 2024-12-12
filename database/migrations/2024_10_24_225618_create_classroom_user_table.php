<?php

use App\Models\Classroom;
use App\Models\User;
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
        Schema::create('classroom_user', function (Blueprint $table) {
            $table->string('class_code', 40)->comment('Mã lớp học');
            $table->foreign('class_code')->references('class_code')->on('classrooms')
                ->cascadeOnDelete()->cascadeOnUpdate();

            $table->string('user_code', 20)->comment('Mã sinh viên');
            $table->foreign('user_code')->references('user_code')->on('users')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->boolean('is_qualified')->default(false);
            $table->primary(['class_code', 'user_code']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classroom_user');
    }
};
