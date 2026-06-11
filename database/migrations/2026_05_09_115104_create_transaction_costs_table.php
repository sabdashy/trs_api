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
        Schema::create('transaction_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_transaction_id')->constrained()->onDelete('cascade');
            $table->string('cost_type');
            $table->enum('calculation_type', [
                'fixed',
                'per_qty'
            ]);
            $table->decimal('amount', 15, 2);
            $table->decimal('total_amount', 15, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_costs');
    }
};
