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
        Schema::create('asset_configuration', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('asset_id');
             $table->unsignedBigInteger('attribute_id');
            $table->foreign('asset_id')->references('id')->on('asset_types');
            $table->foreign('attribute_id')->references('id')->on('attribute');
            $table->timestamps();
            $table->unique(['asset_id', 'attribute_id']);
            $table->softDeletes();
        });
        Schema::table('attribute', function (Blueprint $table) {
            $table->softDeletes(); // Adds the deleted_at column
        });
        Schema::table('attribute_options', function (Blueprint $table) {
            $table->softDeletes(); // Adds the deleted_at column
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_configuration');
    }
};
