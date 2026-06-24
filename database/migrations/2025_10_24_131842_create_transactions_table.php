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
        // Schema::create('transactions', function (Blueprint $table) {
        //     $table->id();
        //     $table->enum('transaction_type', ['credit', 'debit']); // Better than integer for readability
        //     $table->decimal('amount', 10, 2); // Use decimal for money
        //     $table->date('transaction_date');
        //     $table->mediumText('remarks')->nullable();
        //     // $table->string('bill_path')->nullable(); // Optional file upload
        //     $table->timestamps();
        // });

        // Schema::create('transaction_items', function (Blueprint $table) {
        //     $table->id();
        //     $table->unsignedInteger('transaction_id');
        //     $table->unsignedInteger('expense_item_id');
        //     $table->unsignedBigInteger('amount');         
        //     $table->timestamps();
        //     $table->foreign('transaction_id')->references('id')->on('transactions')->onDelete('cascade');
        //     $table->foreign('expense_item_id')->references('id')->on('expense_items');
        // });
        // Schema::create('transaction_items', function (Blueprint $table) {
        //     $table->id();
        //     $table->foreignId('transaction_id')->constrained()->onDelete('cascade');
        //     $table->foreignId('expense_item_id')->constrained('expense_items');
        //     $table->decimal('amount', 10, 2);
        //     $table->timestamps();
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('transaction_items');
    }
};
