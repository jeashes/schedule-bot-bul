<?php

namespace App\Service\OpenAi;

use OpenAI\Laravel\Facades\OpenAI;

class DataCreator
{
    private const MODEL = 'gpt-4o-mini';

    public function aiAnalyze(string $systemPrompt, string $body): string|null
    {
        $response = OpenAI::chat()->create([
            'model' => self::MODEL,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $body]
            ]
        ]);

        return $response->choices[0]->message->content;
    }
}
