<?php
// app/Services/Chatbot/ChatService.php
namespace App\Services\Chatbot;

use App\Models\{ChatSession, ChatMessage, ChatIntent, ChatResponse};
use App\Services\Chatbot\IntentDetector;
use App\Services\HR\LeaveService;

class ChatService
{
    public function handleUserMessage(int $userId, string $text): array
    {
        $session = ChatSession::firstOrCreate(['user_id' => $userId, 'status' => 'active']);
        ChatMessage::create([
            'chat_session_id' => $session->id,
            'sender' => 'user',
            'content' => $text,
        ]);

        $intentData = app(IntentDetector::class)->detect($text, $userId);

        if (!$intentData) {
            $reply = "I couldn’t understand that. Try rephrasing or ask about leave, attendance, or payslips.";
            $botMsg = ChatMessage::create([
                'chat_session_id' => $session->id,
                'sender' => 'bot',
                'content' => $reply,
            ]);
            return ['reply' => $reply, 'session_id' => $session->id];
        }

        // Example: leave balance intent
        if ($intentData['name'] === 'leave_status') {
            $balance = app(LeaveService::class)->getBalance($userId);
            $template = ChatResponse::where('chat_intent_id', $intentData['id'])->value('template')
                ?? "Your leave balance is: {{balance}}";
            $reply = str_replace('{{balance}}', (string)$balance, $template);
        } else {
            $reply = "Okay, I noted your request: {$intentData['name']}.";
        }

        ChatMessage::create([
            'chat_session_id' => $session->id,
            'sender' => 'bot',
            'content' => $reply,
            'extras' => json_encode(['intent' => $intentData]),
        ]);

        return ['reply' => $reply, 'session_id' => $session->id];
    }
}