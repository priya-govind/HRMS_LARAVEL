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
      Schema::create('pm_timesheets', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('employee_id'); // who logged the timesheet
        $table->date('date');                      // restricted to current date
        $table->string('from_time');               // dropdown values
        $table->string('to_time');                 // dropdown values
        $table->unsignedBigInteger('project_id');
        $table->unsignedBigInteger('module_id')->nullable();
        $table->unsignedBigInteger('task_id')->nullable();
        $table->string('custom_task')->nullable(); 
        $table->text('comments')->nullable();
        $table->integer('duration')->nullable();
        $table->timestamps();

        $table->foreign('employee_id')->references('id')->on('users')->onDelete('cascade');
        $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
        $table->foreign('module_id')->references('id')->on('proj_modules')->onDelete('cascade');
        $table->foreign('task_id')->references('id')->on('pm_tasks')->onDelete('cascade');
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pm_timesheets');
    }
};
