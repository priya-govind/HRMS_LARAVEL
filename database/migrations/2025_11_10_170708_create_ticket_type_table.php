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
        Schema::create('ticket_types', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_type');
            $table->smallInteger('ticket_type_active')->unsigned()->default(1); 
            $table->timestamps();
        });
        Schema::table('ticket_types', function (Blueprint $table) {
          $table->softDeletes(); 
       });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_types');
    }
};
