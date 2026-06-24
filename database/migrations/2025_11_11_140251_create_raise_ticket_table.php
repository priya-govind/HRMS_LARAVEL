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
        Schema::create('raise_ticket', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_type_id');
            $table->unsignedBigInteger('problem_type_id');
             $table->string('ticket_name');
             $table->mediumText('ticket_desc')->nullable();
            $table->unsignedBigInteger('ticket_raised_by');
            $table->unsignedBigInteger('ticket_solved_by')->default(0);
            $table->unsignedBigInteger('ticket_status')->default(8);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('raise_ticket');
    }
};
