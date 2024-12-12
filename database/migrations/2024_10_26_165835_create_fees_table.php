<?php

use App\Models\Category;
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
        Schema::create('fees', function (Blueprint $table) {
            $table->id();
            $table->string('user_code',20);
            $table->foreign('user_code')->references('user_code')
                  ->on('users')->cascadeOnDelete()->restrictOnUpdate();
            $table->decimal('total_amount', 10, 2);
            $table->decimal('amount', 10, 2)->default(0);
            $table->date('start_date');
            $table->date('due_date');
            $table->string('semester_code',40);
            $table->foreign('semester_code')->references('cate_code')->on('categories')
            ->restrictOnDelete()->restrictOnUpdate();
            $table->enum('status', ['pending', 'paid', 'unpaid'])->default('unpaid');
            $table->unique(['user_code', 'semester_code']);
            $table->timestamps();
        });
    }
    // fees: 	fee_id
    // student_id
    // amount:		(số tiền phải đóng)
    // overpaid_amount
    // start_date:	(ngày bắt đầu)
    // due_date:	(ngày hạn đóng)
    // status: 	(trạng thái)
    // paid_date:

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fees');
    }
};
