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
         Schema::create('ticket_assign_members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id');
            $table->unsignedBigInteger('owner_id');
            $table->unsignedBigInteger('assign_mem_id');
            $table->Text('comments_head')->nullable()->default(null);
            $table->Text('reply_to')->nullable()->default(null);
            $table->foreign('ticket_id')->references('id')->on('raise_ticket')->onDelete('cascade');
            $table->foreign('owner_id')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_assign_members');
    }
};
