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
        Schema::create('muted_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');       
            $table->unsignedBigInteger('muted_user_id'); 
            $table->timestamps();

            $table->unique(['user_id', 'muted_user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('muted_users');
    }
};
