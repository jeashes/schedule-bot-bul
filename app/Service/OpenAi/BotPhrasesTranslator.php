<?php

namespace App\Service\OpenAi;

class BotPhrasesTranslator
{
    public function __construct(private readonly DataCreator $dataCreator) {}

    public function translate(string $targetLang, string $sourcePhrases): array
    {
        $systemPrompt = <<<PROMPT
        You are a professional translator. Translate the following bot messages from English to {$targetLang}.
        Maintain all Markdown formatting (*bold*, _italic_), emoji, and placeholder variables (like :name, :email, :hours, :title, :url).
        Keep the same tone and style as the original messages.

        Source phrases:
        {$sourcePhrases}

        Requirements:
        1. Keep all placeholder variables unchanged (e.g., :name, :email)
        2. Preserve all Markdown formatting
        3. Keep all emoji
        4. Maintain line breaks (\n)
        5. Return response in this exact JSON format:
        [
            {
                "phrase_key": "same as source phrase key",
                "language_code": "{$targetLang}",
                "phrase_text": "translated text"
            },
            ...
        ]

        Translate all messages maintaining the original meaning and formatting.
        PROMPT;

        $data = json_decode($this->dataCreator->aiAnalyze($systemPrompt, ''), true);

        return $data;
    }
}
