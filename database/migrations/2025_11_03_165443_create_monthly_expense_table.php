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
        Schema::create('monthly_expense', function (Blueprint $table) {
            $table->id();
            $table->enum('transaction_type', ['credit', 'debit']); // Better than integer for readability
            $table->decimal('trans_amount', 10, 2); // Use decimal for money
            $table->date('transaction_date');
            $table->mediumText('remarks')->nullable();
            $table->decimal('available_amt', 10, 2);
            $table->enum('payment_type', ['cash', 'gpay','card'])->nullable();
            $table->string('bill_refer')->nullable(); // Optional file upload
            $table->boolean('is_deleted')->default(false);
            $table->boolean('last_entry')->default(false);
            $table->timestamps();
        });
        Schema::create('monthly_expense_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_id')
                ->constrained('monthly_expense')
                ->onDelete('cascade');
            $table->foreignId('expense_item_id')
                ->constrained('expense_items');
            $table->decimal('exp_amount', 10, 2);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_expense');
        Schema::dropIfExists('monthly_expense_items');
        
    }
};
