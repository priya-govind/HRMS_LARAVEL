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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('task_name');
            $table->unsignedBigInteger('proj_typ_id');
            $table->unsignedBigInteger('proj_id');
            $table->date('startDate');
            $table->date('endDate');
            $table->string('team_typ_id');
            $table->tinyInteger('task_status')->default(1);
            $table->timestamps();
            $table->unsignedBigInteger('created_by');
            $table->foreign('proj_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('proj_typ_id')->references('id')->on('project_type');
        });

        Schema::create('task_assign_team', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_id');
            $table->unsignedBigInteger('team_id');
            $table->timestamps();
            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
            $table->foreign('team_id')->references('id')->on('teams');
        });
        Schema::create('task_assign_emp', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_id');
            $table->unsignedBigInteger('employee_id');
            $table->timestamps();
            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
            $table->foreign('employee_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_assign_emp');
        Schema::dropIfExists('task_assign_team');
        Schema::dropIfExists('tasks');
    }
};
