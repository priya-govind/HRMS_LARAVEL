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
        Schema::create('leave_info', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('emp_id');
            $table->date('from_dt');
            $table->date('to_dt');
            $table->tinyInteger('leave_type')->default(1)->comment('1 = Leave, 2 = Permission');
            $table->time('from_time')->nullable();
            $table->time('to_time')->nullable();
            $table->text('reason')->nullable();
            $table->text('reason_status')->nullable();
            $table->unsignedBigInteger('approved_by');
            $table->tinyInteger('leave_status');
            $table->foreign('approved_by')->references('id')->on('users');
            $table->foreign('emp_id')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_info');
    }
};
