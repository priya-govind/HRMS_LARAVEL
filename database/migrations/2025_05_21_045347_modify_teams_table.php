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
         Schema::table('teams', function (Blueprint $table) {
             $table->tinyInteger('proj_type');
           });
            Schema::table('team_members', function (Blueprint $table) {
              $table->tinyInteger('proj_type');
              $table->tinyInteger('ctrl_status')->default(0);
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
