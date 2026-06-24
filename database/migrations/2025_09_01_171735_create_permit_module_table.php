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
        Schema::create('permit_module', function (Blueprint $table) {
            $table->id();
             $table->unsignedBigInteger('emp_id');
             $table->unsignedBigInteger('assigned_by');
             $table->string('module_name');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permit_module');
    }
};
