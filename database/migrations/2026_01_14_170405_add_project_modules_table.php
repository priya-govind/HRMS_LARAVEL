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
       Schema::create('proj_modules', function (Blueprint $table) {
            $table->id();
            $table->string('module_name');
            $table->unsignedBigInteger('proj_id');
            $table->mediumText('desc');
            $table->foreign('proj_id')->references('id')->on('projects')->onDelete('cascade');
       });
       Schema::create('module_assign_members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('proj_id');
            $table->unsignedBigInteger('module_id');
            $table->unsignedBigInteger('emp_id');
            $table->foreign('proj_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('module_id')->references('id')->on('proj_modules');
            $table->foreign('emp_id')->references('id')->on('users');
       });
       
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
          Schema::dropIfExists('proj_modules');
          Schema::dropIfExists('module_assign_members');
    }
};
