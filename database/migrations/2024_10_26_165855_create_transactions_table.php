<?php

use App\Models\Fee;
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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(model: Fee::class)->constrained();
            $table->date('payment_date');
            $table->decimal('amount_paid',12,2);
            $table->enum('payment_method',['transfer','cash']);
            $table->enum('type', ['add','deduct']);
            $table->string('receipt_number');
            $table->boolean('is_deposit')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
