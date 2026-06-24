<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChatbotSeeder extends Seeder
{
    public function run()
    {
        $intents = [
            [
                'name' => 'attendance',
                'patterns' => ['attendance', 'attendance_info', 'attendance report'],
                'response' => 'Here is your attendance information...'
            ],
            [
                'name' => 'leave',
                'patterns' => ['leave', 'leave report', 'leave info'],
                'response' => 'Here is your leave information...'
            ],
            [
                'name' => 'timesheet',
                'patterns' => ['timesheet', 'timesheet report', 'work log'],
                'response' => 'Here is your timesheet information...'
            ],
            [
                'name' => 'greeting',
                'patterns' => ['hi', 'hello', 'hi bot', 'hello bot'],
                'response' => 'Hello! How may I help you today?'
            ],
        ];

        foreach ($intents as $intent) {
            $intentId = DB::table('chatbot_intents')->insertGetId([
                'name' => $intent['name'],
                'patterns' => json_encode($intent['patterns']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('chatbot_responses')->insert([
                'chatbot_intent_id' => $intentId,
                'template' => $intent['response'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}