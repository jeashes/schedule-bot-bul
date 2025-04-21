<?php

namespace App\Managers\Telegram;

use App\Models\Mongo\BotPhrases;
use App\Repository\BotPhraseRepository;
use App\Service\OpenAi\BotPhrasesTranslator;
use Illuminate\Support\Collection;

class BotPhrasesManager
{
    public function __construct(
        private readonly BotPhraseRepository $botPhraseRepository,
        private readonly BotPhrasesTranslator $translator,
    ) {}

    public function preparePhrasesByLanguage(string $languageCode): Collection
    {
        $phrases = $this->botPhraseRepository->getPhrasesByLanguageCode($languageCode)->toArray();

        if (! empty($phrases)) {
            return $this->botPhraseRepository->savePhrases($phrases);
        }

        $defatultPhrases = $this->botPhraseRepository->getPhrasesByLanguageCode(BotPhrases::DEFAULT_LANGUAGE_CODE);

        $translated = $this->translator->translate($languageCode, json_encode($defatultPhrases));

        return $this->botPhraseRepository->savePhrases($translated);
    }
}
