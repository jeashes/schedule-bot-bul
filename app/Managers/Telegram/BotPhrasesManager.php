<?php

namespace App\Managers\Telegram;

use App\Repository\BotPhraseRepository;
use App\Service\OpenAi\BotPhrasesTranslator;
use Illuminate\Support\Collection;

class BotPhrasesManager
{
    public function __construct(
        private readonly BotPhraseRepository $botPhraseRepository,
        private readonly BotPhrasesTranslator $translator,
    ) {}

    public function preparePhrasesByLanguage(string $languageCode): array|Collection
    {
        $phrases = $this->botPhraseRepository->getPhrasesByLanguageCode($languageCode)->toArray();

        if (! empty($phrases)) {
            return $this->botPhraseRepository->savePhrases($phrases);
        }

        $translated = $this->translator->translate($languageCode, json_encode($phrases));

        return $this->botPhraseRepository->savePhrases($translated);
    }
}
