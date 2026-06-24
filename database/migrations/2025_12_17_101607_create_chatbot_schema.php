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
       
            // database/migrations/xxxx_create_chatbot_tables.php
        Schema::create('chatbot_sessions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('status')->default('active'); // active|closed
            $t->json('metadata')->nullable();
            $t->timestamps();
        });

        Schema::create('chatbot_messages', function (Blueprint $t) {
            $t->id();
            $t->foreignId('chatbot_session_id')->constrained()->cascadeOnDelete();
            $t->enum('sender', ['user', 'bot']);
            $t->text('content');
            $t->json('extras')->nullable(); // detected intent, confidence, etc.
            $t->timestamps();
        });

        Schema::create('chatbot_intents', function (Blueprint $t) {
            $t->id();
            $t->string('name')->unique(); // e.g., leave_status, payslip_help
            $t->json('patterns'); // ["leave status", "my leave balance", ...]
            $t->json('examples')->nullable();
            $t->json('roles_allowed')->nullable(); // ["employee","hr","manager"]
            $t->timestamps();
        });

        Schema::create('chatbot_responses', function (Blueprint $t) {
            $t->id();
            $t->foreignId('chatbot_intent_id')->constrained()->cascadeOnDelete();
            $t->text('template'); // e.g., "Your leave balance is: {{balance}}"
            $t->json('variables')->nullable(); // ["balance"]
            $t->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chatbot_sessions');
        Schema::dropIfExists('chatbot_messages');
        Schema::dropIfExists('chatbot_intents');
        Schema::dropIfExists('chatbot_responses');
    }
};
