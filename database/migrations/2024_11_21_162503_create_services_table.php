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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('user_code',20)->comment('Mã sinh viên');
            $table->foreign('user_code')->references('user_code')->on('users')
                    ->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('service_name');
            $table->text('content');
            $table->enum('status',['pending','paid','approved','rejected',])->default('pending');
            $table->text('reason')->nullable()->default(null);
            $table->decimal('amount',15,0)->default(0);
            $table->string('file_path')->nullable();
            $table->timestamps();
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('service');
    }
};
