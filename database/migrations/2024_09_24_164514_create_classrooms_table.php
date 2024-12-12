<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('classrooms', function (Blueprint $table) {
            $table->id();
            $table->string('class_code',40)->unique()->comment('Mã lớp');
            $table->string('class_name',50)->comment('Tên lớp');
            $table->boolean('is_automatic')->default(true);
            $table->text('description')->comment('Mô tả')->nullable();
            $table->boolean('is_active')->default(true);

            $table->string('user_code',20)->nullable()->comment('Mã giảng viên');

            $table->foreign('user_code')
                    ->references('user_code')
                    ->on('users')
                    ->cascadeOnDelete()
                        ->cascadeOnUpdate();

            $table->string('subject_code',40)
                  ->comment('Mã môn học');

            $table->foreign('subject_code')
                    ->references('subject_code')
                    ->on('subjects')
                    ->restrictOnDelete()
                    ->cascadeOnUpdate();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('classrooms');
    }
};
