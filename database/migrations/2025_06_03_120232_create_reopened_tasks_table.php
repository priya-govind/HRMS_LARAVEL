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
        Schema::create('reopened_tasks', function (Blueprint $table) {
            $table->id();
            $table->Integer('task_id'); 
            $table->Integer('emp_id');
            $table->Integer('team_id');
            $table->TinyInteger('reopen_type'); 
            $table->TinyInteger('ctrl_status'); 
            $table->TinyInteger('task_status')->default(0);//if set 1=>completed else inprogress
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reopened_tasks');
    }
};
