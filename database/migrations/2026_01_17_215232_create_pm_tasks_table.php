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
         Schema::create('pm_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('task_name');
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('module_id');
            $table->date('startDate');
            $table->date('endDate');
            $table->mediumText('task_desc');
            $table->tinyInteger('task_status')->default(1);
            $table->timestamps();
            $table->unsignedBigInteger('created_by');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('module_id')->references('id')->on('proj_modules');
        });
         Schema::create('pm_task_assign_emp', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_id');
            $table->unsignedBigInteger('employee_id');
            $table->mediumText('emp_comments');
            $table->timestamps();
            $table->foreign('task_id')->references('id')->on('pm_tasks')->onDelete('cascade');
            $table->foreign('employee_id')->references('id')->on('users');
        });
         Schema::create('pm_task_uploads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_id');
            $table->unsignedBigInteger('uploaded_by');
            $table->string('original_name');
            $table->string('stored_name');   // auto-generated filename
            $table->string('path');          // storage path
            $table->string('mime_type');
            $table->integer('size');
            $table->timestamps();
            $table->foreign('task_id')->references('id')->on('pm_tasks')->onDelete('cascade');
            $table->foreign('uploaded_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pm_tasks');
        Schema::dropIfExists('pm_task_assign_emp');
        Schema::dropIfExists('pm_task_uploads');
    }
};
