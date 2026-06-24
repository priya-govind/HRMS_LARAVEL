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
          Schema::table('tasks', function (Blueprint $table) {
             $table->dateTime('startDate', 0)->change();
             $table->dateTime('endDate', 0)->change();
             $table->softDeletes();
           });
           Schema::table('task_assign_emp', function (Blueprint $table) {
             $table->softDeletes();
           });
          Schema::table('task_assign_team', function (Blueprint $table) {
             $table->softDeletes();
           });
        
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
