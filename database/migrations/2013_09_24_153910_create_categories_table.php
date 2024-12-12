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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('cate_code',40)->unique();
            $table->index('cate_code');
            $table->string('cate_name',50)->unique();
            $table->text('value')->nullable();
            $table->string('image')->nullable();
            $table->text('description')->nullable();
            $table->string('parent_code',40)->index()->nullable();
            $table->foreign('parent_code')->references('cate_code')->on('categories')
                    ->restrictOnDelete()->cascadeOnUpdate();
                    
            $table->string('type');
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
            $table->index(['is_active', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('categories');
    }
};
