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
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->string('asset_name');
            $table->unsignedBigInteger('asset_type'); // e.g., Laptop, Monitor, License
            $table->unsignedBigInteger('asset_brand'); // e.g., Laptop, Monitor, License
            $table->string('serial_number')->unique();
            $table->enum('status', ['available', 'assigned', 'damaged', 'retired'])->default('available');
            $table->foreign('asset_type')->references('id')->on('item_types');
            $table->foreign('asset_brand')->references('id')->on('brands');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::dropIfExists('inventories');
    }
};
