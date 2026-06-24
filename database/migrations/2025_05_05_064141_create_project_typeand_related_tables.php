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
        Schema::create('project_type', function (Blueprint $table) {
            $table->id();
            $table->string('proj_typ_name');
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('proj_type');
            $table->string('proj_name');
            $table->tinyInteger('proj_status');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('proj_type')->references('id')->on('project_type');
        });
        Schema::create('team_type', function (Blueprint $table) {
            $table->id();
            $table->string('team_typ_name');
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_type');
            $table->string('team_name');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('team_type')->references('id')->on('team_type');

        });
        Schema::create('team_members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id');
            $table->unsignedBigInteger('emp_id');
            $table->timestamps();
            $table->foreign('team_id')->references('id')->on('teams');
            $table->foreign('emp_id')->references('id')->on('users');
        });

        Schema::create('project_status', function (Blueprint $table) {
            $table->id();
            $table->string('proj_status_name');
            $table->softDeletes();
        });
        Schema::table('projects', function (Blueprint $table) {
            $table->foreign('proj_status')->references('id')->on('project_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_type');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('team_type');
        Schema::dropIfExists('teams');
        Schema::dropIfExists('team_members');
    }
};
