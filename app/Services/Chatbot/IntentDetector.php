<?php
namespace App\Services\Chatbot;

use App\Models\ChatIntent;

class IntentDetector
{
    public function detect(string $text, int $userId): ?array
    {
        $intents = ChatIntent::all();
        foreach ($intents as $intent) {
            foreach ($intent->patterns as $pattern) {
                if ($this->matches($text, $pattern)) {
                    return ['id' => $intent->id, 'name' => $intent->name];
                }
            }
        }
        return null;
    }

    protected function matches(string $text, string $pattern): bool
    {
        $text = mb_strtolower(trim($text));
        $pattern = mb_strtolower(trim($pattern));
        if ($text === $pattern) return true;
        // simple fuzzy match
        return levenshtein($text, $pattern) <= 3 || str_contains($text, $pattern);
    }
}
