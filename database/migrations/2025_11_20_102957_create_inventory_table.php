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
        Schema::create('inventory_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('inventory_id');
            $table->unsignedBigInteger('employee_id');
            $table->foreign('inventory_id')->references('id')->on('raise_ticket')->onDelete('cascade');
            $table->foreign('employee_id')->references('id')->on('users');
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventories');
        Schema::dropIfExists('inventory_assignments');
    }
};
