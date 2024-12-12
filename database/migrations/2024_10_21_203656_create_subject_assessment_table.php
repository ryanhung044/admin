<?php

use App\Models\AssessmentItem;
use App\Models\Subject;
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
        Schema::create('subject_assessment', function (Blueprint $table) {
            $table->id();
            $table->string('subject_code', 40);
            $table->foreign('subject_code')->references('subject_code')->on('subjects')
            ->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('assessment_code', 40);
            $table->foreign('assessment_code')->references('assessment_code')->on('assessment_items')
            ->cascadeOnDelete()->cascadeOnUpdate();
            $table->unique(['subject_code','assessment_code']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subject_assessment');
    }
};
