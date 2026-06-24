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
        Schema::create('punch_attendance', function (Blueprint $table) {
            $table->id();
            $table->date('punch_date');
            $table->string('employee_name');
            $table->string('employee_code');
            $table->char('status', length: 10);
            $table->time('checkin_time')->nullable();
            $table->time('checkout_time')->nullable();
            $table->time('duration')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('punch_attendance');
    }
};
