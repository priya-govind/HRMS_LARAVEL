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
        Schema::table('asset_types', function (Blueprint $table) {
            $table->softDeletes(); // Adds the deleted_at column
        });
        Schema::table('accessory_types', function (Blueprint $table) {
            $table->softDeletes(); // Adds the deleted_at column
        });
        Schema::table('component_types', function (Blueprint $table) {
            $table->softDeletes(); // Adds the deleted_at column
        });
        Schema::table('licenses_types', function (Blueprint $table) {
            $table->softDeletes(); // Adds the deleted_at column
        });
         Schema::table('brands', function (Blueprint $table) {
            $table->softDeletes(); // Adds the deleted_at column
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_types', function (Blueprint $table) {
            $table->dropSoftDeletes(); // Adds the deleted_at column
        });
        Schema::table('accessory_types', function (Blueprint $table) {
            $table->dropSoftDeletes(); // Adds the deleted_at column
        });
        Schema::table('component_types', function (Blueprint $table) {
            $table->dropSoftDeletes(); // Adds the deleted_at column
        });
        Schema::table('licenses_types', function (Blueprint $table) {
            $table->dropSoftDeletes(); // Adds the deleted_at column
        });
         Schema::table('brands', function (Blueprint $table) {
            $table->dropSoftDeletes(); // Adds the deleted_at column
        });
    }
};
