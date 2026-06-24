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
        Schema::table('project_status', function (Blueprint $table) {
              $table->Integer('emp_set_status')->unsigned()->default(0); 
              $table->Integer('task_set_status')->unsigned()->default(0); 
              $table->Integer('proj_set_status')->unsigned()->default(0);
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
