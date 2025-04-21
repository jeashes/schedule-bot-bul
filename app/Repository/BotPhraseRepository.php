<?php

namespace App\Repository;

use App\Models\Mongo\BotPhrases;
use Illuminate\Support\Collection;

class BotPhraseRepository
{
    public function getPhrasesByLanguageCode(string $languageCode): Collection
    {
        return BotPhrases::query()->where('language_code', $languageCode)->get();
    }

    public function getPhraseByKey(string $phraseKey, string $languageCode): BotPhrases
    {
        return BotPhrases::query()->where(['language_code' => $languageCode, 'phrase_key' => $phraseKey])->first();
    }

    public function savePhrases(array $phrases): array|Collection
    {
        $updatedPhrases = [];
        foreach ($phrases as $phrase) {
            $updatedPhrases[] = BotPhrases::query()->where($phrase)->firstOrCreate();
        }

        return $updatedPhrases;
    }
}
