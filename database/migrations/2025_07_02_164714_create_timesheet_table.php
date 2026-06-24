<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Date ,Day,  From, To, Project, Module, Function
     */
    public function up(): void
    {
        Schema::create('timesheet', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('emp_id');
            $table->date('create_dt');
            $table->string('day');
            $table->time('from_time');
            $table->time('to_time');
            $table->unsignedBigInteger('project_id');
            $table->mediumText('module');
            $table->longText('description');
            $table->timestamps();
            $table->foreign('emp_id')->references('id')->on('users');
            $table->foreign('project_id')->references('id')->on('projects');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timesheet');
    }
};
