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
          Schema::table('transactions', function (Blueprint $table) {
            $table->enum('payment_type', ['cash', 'gpay', 'card'])->after('existing_column');
            $table->string('bill_refer')->nullable()->after('payment_type');
        });
        // Schema::table('transactions', function (Blueprint $table) {
        //     $table->enum('transaction_type', ['credit', 'debit'])->change();
        //     $table->decimal('amount', 10, 2)->change();
        //     $table->mediumText('remarks')->nullable()->change();
        //  });
        // Schema::table('transaction_items', function (Blueprint $table) {
        //     $table->foreignId('transaction_id')->constrained()->onDelete('cascade');
        //     $table->foreignId('expense_item_id')->constrained('expense_items');
        //     $table->decimal('amount', 10, 2);
        //     $table->decimal('available_amt', 10, 2);
        // });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
