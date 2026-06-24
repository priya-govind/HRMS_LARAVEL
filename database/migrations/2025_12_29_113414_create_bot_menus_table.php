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
        Schema::create('bot_menus', function (Blueprint $table) {
            $table->id();
            $table->string('bot_name');
            $table->unsignedBigInteger('parent_id');
            $table->string('command');
            $table->boolean('is_active')->default(1);
            $table->boolean('support_access')->default(1);
            $table->unsignedBigInteger('order_by')->default(0);
            $table->timestamps();
        });

        Schema::create('role_bot_menus_permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('bot_id');
            $table->unsignedBigInteger('permission_id');
            $table->timestamps();
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->foreign('bot_id')->references('id')->on('bot_menus')->onDelete('cascade');
            $table->foreign('permission_id')->references('id')->on('permissions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bot_menus');
        Schema::dropIfExists('role_bot_menus_permissions');
    }
};
