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
        Schema::create('birthday_calendar', function (Blueprint $table) {
            $table->id();
             $table->string('employee_name', length: 250);
              $table->string('employee_code');
             $table->date('birth_date');
             $table->date('last_alerted_date')->nullable();
            $table->timestamps();
        });
        //  Schema::table('punch_attendance', function (Blueprint $table) {
        //     $table->enum('team_type', ['Development', 'IT', 'TDS'])->after('employee_code');
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('birthday_calendar');
    }
};
