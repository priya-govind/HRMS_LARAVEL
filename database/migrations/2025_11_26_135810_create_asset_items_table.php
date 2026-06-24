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
        Schema::create('asset_items', function (Blueprint $table) {
            $table->id();
            $table->string('item_name');
            $table->unsignedBigInteger('item_type'); // e.g., Laptop, Monitor, License
            $table->unsignedBigInteger('item_category'); 
            $table->unsignedBigInteger('item_brand'); // e.g., Laptop, Monitor, License
            $table->string('serial_number')->unique();
            $table->enum('status', ['available', 'assigned', 'damaged', 'retired'])->default('available');
            $table->decimal('purchased_amount', 12, 2)->nullable();
            $table->date('purchased_at')->nullable();
            $table->foreign('item_brand')->references('id')->on('brands');
            $table->date('expiry_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_items');
    }
};
